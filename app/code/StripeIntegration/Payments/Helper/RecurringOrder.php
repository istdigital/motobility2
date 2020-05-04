<?php

namespace StripeIntegration\Payments\Helper;

use StripeIntegration\Payments\Helper\Logger;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use StripeIntegration\Payments\Exception\SCANeededException;
use Magento\Framework\Exception\LocalizedException;
use StripeIntegration\Payments\Exception\WebhookException;

class RecurringOrder
{
    public $invoice = null;

    public function __construct(
        \StripeIntegration\Payments\Helper\Generic $paymentsHelper,
        \StripeIntegration\Payments\Model\Config $config,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Store\Model\Store $storeManager,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory,
        \Magento\Sales\Model\AdminOrder\Create $adminOrderCreateModel,
        \StripeIntegration\Payments\Helper\Webhooks $webhooksHelper
    ) {
        $this->paymentsHelper = $paymentsHelper;
        $this->config = $config;
        $this->quoteFactory = $quoteFactory;
        $this->storeManager = $storeManager;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->cartManagementInterface = $cartManagementInterface;
        $this->customerFactory = $customerFactory;
        $this->adminOrderCreateModel = $adminOrderCreateModel;
        $this->webhooksHelper = $webhooksHelper;
    }

    public function createFromInvoiceId($invoiceId)
    {
        $this->invoice = $invoice = \Stripe\Invoice::retrieve(['id' => $invoiceId, 'expand' => ['subscription']]);

        if (empty($invoice->subscription->metadata["Order #"]))
            throw new WebhookException("The subscription on invoice $invoiceId is not associated with a Magento order", 202);

        $orderIncrementId = $invoice->subscription->metadata["Order #"];

        if (empty($invoice->subscription->metadata["Product ID"]))
            throw new WebhookException("The subscription on invoice $invoiceId is not associated with any Magento product ID", 202);

        $productId = $invoice->subscription->metadata["Product ID"];
        $originalOrder = $this->paymentsHelper->loadOrderByIncrementId($orderIncrementId);

        if (!$originalOrder->getId())
            throw new WebhookException("Error: Could not load original order #$orderIncrementId", 202);

        $invoiceDetails = $this->getInvoiceDetails($invoice, $originalOrder);

        $newOrder = $this->reOrder($originalOrder, $invoiceDetails);

        return $newOrder;
    }

    public function getInvoiceDetails($invoice, $order)
    {
        $details = [
            "invoice_amount" => $this->convertToMagentoAmount($invoice->amount_paid, $invoice->currency),
            "base_invoice_amount" => 0,
            "invoice_currency" => $invoice->currency,
            "invoice_tax_percent" => $invoice->tax_percent,
            "invoice_tax_amount" => $this->convertToMagentoAmount($invoice->tax, $invoice->currency),
            "payment_intent" => $invoice->payment_intent,
            "shipping_amount" => 0,
            "base_shipping_amount" => 0,
            "shipping_currency" => null,
            "shipping_tax_percent" => 0,
            "shipping_tax_amount" => 0,
            "initial_fee_amount" => 0,
            "base_initial_fee_amount" => 0,
            "initial_fee_currency" => null,
            "initial_fee_tax_percent" => 0,
            "initial_fee_tax_amount" => 0,
            "discount_amount" => 0,
            "discount_percent" => 0,
            "products" => [],
            "shipping_address" => []
        ];

        foreach ($invoice->lines->data as $invoiceLineItem)
        {
            if (isset($invoiceLineItem->metadata["Product ID"]))
            {
                $product = [];
                $product["id"] = $invoiceLineItem->metadata["Product ID"];
                $product["amount"] = $this->convertToMagentoAmount($invoiceLineItem->amount, $invoiceLineItem->currency);
                $product["qty"] = $invoiceLineItem->quantity;
                $product["currency"] = $invoiceLineItem->currency;
                $product["tax_percent"] = 0;
                $product["tax_amount"] = 0;

                if (isset($invoiceLineItem->tax_rates[0]->percentage))
                    $product["tax_percent"] = $invoiceLineItem->tax_rates[0]->percentage;

                if (isset($invoiceLineItem->tax_amounts[0]->amount))
                    $product["tax_amount"] = $this->convertToMagentoAmount($invoiceLineItem->tax_amounts[0]->amount, $invoiceLineItem->currency);

                $details["products"][$product["id"]] = $product;

                if (!empty($invoiceLineItem->metadata["Shipping Street"]))
                {
                    $details["shipping_address"] = [
                        'firstname' => $invoiceLineItem->metadata["Shipping First Name"],
                        'lastname' => $invoiceLineItem->metadata["Shipping Last Name"],
                        'company' => $invoiceLineItem->metadata["Shipping Company"],
                        'street' => $invoiceLineItem->metadata["Shipping Street"],
                        'city' => $invoiceLineItem->metadata["Shipping City"],
                        'postcode' => $invoiceLineItem->metadata["Shipping Postcode"],
                        'telephone' => $invoiceLineItem->metadata["Shipping Telephone"],
                    ];
                }
            }
            // Can also be "Shipping cost" in older versions of the module
            else if (strpos($invoiceLineItem->description, "Shipping") === 0)
            {
                $details["shipping_amount"] = $this->convertToMagentoAmount($invoiceLineItem->amount, $invoiceLineItem->currency);
                $details["shipping_currency"] = $invoiceLineItem->currency;

                if (isset($invoiceLineItem->tax_rates[0]->percentage))
                    $details["shipping_tax_percent"] = $invoiceLineItem->tax_rates[0]->percentage;

                if (isset($invoiceLineItem->tax_amounts[0]->amount))
                    $details["shipping_tax_amount"] = $this->convertToMagentoAmount($invoiceLineItem->tax_amounts[0]->amount, $invoiceLineItem->currency);
            }
            else if (stripos($invoiceLineItem->description, "Initial fee") === 0)
            {
                $details["initial_fee_amount"] = $this->convertToMagentoAmount($invoiceLineItem->amount, $invoiceLineItem->currency);
                $details["initial_fee_currency"] = $invoiceLineItem->currency;

                if (isset($invoiceLineItem->tax_rates[0]->percentage))
                    $details["initial_fee_tax_percent"] = $invoiceLineItem->tax_rates[0]->percentage;

                if (isset($invoiceLineItem->tax_amounts[0]->amount))
                    $details["initial_fee_tax_amount"] = $this->convertToMagentoAmount($invoiceLineItem->tax_amounts[0]->amount, $invoiceLineItem->currency);
            }
            else
            {
                $this->webhooksHelper->log("Invoice $invoiceId includes an item which cannot be recognized as a subscription: " . $invoiceLineItem->description);
            }
        }

        if (empty($details["products"]))
            throw new WebhookException("This invoice does not have any product IDs associated with it", 202);

        if (empty($details["invoice_amount"]))
            throw new WebhookException("Could not determine the subscription amount from the invoice data", 202);

        $details["base_invoice_amount"] = round($details["invoice_amount"] * $order->getBaseToOrderRate(), 2);
        $details["base_shipping_amount"] = round($details["shipping_amount"] * $order->getBaseToOrderRate(), 2);
        $details["base_initial_fee_amount"] = round($details["initial_fee_amount"] * $order->getBaseToOrderRate(), 2);

        foreach ($details["products"] as &$product)
        {
            $product["base_amount"] = round($product["amount"] * $order->getBaseToOrderRate(), 2);
            $product["base_tax_amount"] = round($product["tax_amount"] * $order->getBaseToOrderRate(), 2);
        }

        return $details;
    }

    public function convertToMagentoAmount($amount, $currency)
    {
        $currency = strtolower($currency);
        $cents = 100;
        if ($this->paymentsHelper->isZeroDecimal($currency))
            $cents = 1;
        $amount = ($amount / $cents);
        return $amount;
    }

    public function reOrder($originalOrder, $invoiceDetails)
    {
        $originalOrder->setReordered(true);
        $newOrder = $this->adminOrderCreateModel->initFromOrder($originalOrder);
        $quote = $newOrder->getQuote();
        $this->adjustOrderItems($quote, $invoiceDetails);

        $quote->getPayment()
            ->setQuote($quote)
            ->importData(['method' => 'stripe_payments'])
            ->setAdditionalInformation("is_recurring_subscription", true);

        $quote->setTotalsCollectedFlag(false)->setInventoryProcessed(false);

        $quote->getBillingAddress()->unsetData('cached_items_all');
        $quote->getShippingAddress()->unsetData('cached_items_all');

        $quote->collectTotals()->save();

        if (!$quote->getIsVirtual())
        {
            $quote->getShippingAddress()
                ->addData($invoiceDetails["shipping_address"])
                ->setCollectShippingRates(true)
                ->collectShippingRates()
                ->setShippingMethod($originalOrder->getShippingMethod());
        }

        $order = $newOrder->createOrder();

        $subscriptionId = $this->invoice->subscription->id;
        $orderIncrementId = $originalOrder->getIncrementId();
        $comment = "Recurring order generated from subscription with ID $subscriptionId. ";
        $comment .= "Customer originally subscribed with order #$orderIncrementId. ";
        $order->addStatusToHistory('processing', $comment, false)->save();

        $this->paymentsHelper->invoiceOrder(
            $order,
            $invoiceDetails["payment_intent"],
            \Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE,
            [
                "amount" => $this->invoice->amount_paid,
                "currency" => $this->invoice->currency
            ]);

        return $order;
    }

    public function adjustOrderItems(&$quote, $invoiceDetails)
    {
        $quoteItems = $quote->getAllItems();
        foreach ($quoteItems as $quoteItem)
        {
            if ($this->getIsParentProduct($quoteItem))
            {
                if ($this->getIsSubscriptionParent($quoteItem, $invoiceDetails))
                    continue;
            }

            if (!isset($invoiceDetails["products"][$quoteItem->getProductId()]))
            {
                $quote->removeItem($quoteItem->getItemId())->save();
                continue;
            }
            else
            {
                $product = $invoiceDetails["products"][$quoteItem->getProductId()];
                $qty = $product["qty"];
                $quoteItem->setQtyOrdered($qty);
                $quoteItem->setQty($qty);
                $quoteItem->setQtyToAdd(0);
                $quoteItem->setQtyCanceled(0);
                $quoteItem->setDiscountPercent(0);
                $quoteItem->setDiscountAmount(0);
                $quoteItem->setQtyInvoiced($qty);
                $quoteItem->getProduct()->setQuoteItemQty($qty);
                $quoteItem->getProduct()->setIsSuperMode(true);
                $quoteItem->save();
                unset($invoiceDetails["products"][$quoteItem->getProductId()]);
            }
        }
    }

    public function getIsParentProduct($quoteItem)
    {
        $type = $quoteItem->getProductType();
        return !in_array($type, ["virtual", "simple"]);
    }

    public function getIsSubscriptionParent($quoteItem, $invoiceDetails)
    {
        $qtyOptions = $quoteItem->getQtyOptions();
        foreach ($qtyOptions as $productId => $option)
        {
            if (isset($invoiceDetails["products"][$productId]))
                return true;
        }

        return false;
    }

    public function getAddressDataFrom($address)
    {
        $data = array(
            'prefix' => $address->getPrefix(),
            'firstname' => $address->getFirstname(),
            'middlename' => $address->getMiddlename(),
            'lastname' => $address->getLastname(),
            'email' => $address->getEmail(),
            'suffix' => $address->getSuffix(),
            'company' => $address->getCompany(),
            'street' => $address->getStreet(),
            'city' => $address->getCity(),
            'country_id' => $address->getCountryId(),
            'region' => $address->getRegion(),
            'postcode' => $address->getPostcode(),
            'telephone' => $address->getTelephone(),
            'fax' => $address->getFax(),
            'vat_id' => $address->getVatId()
        );

        return $data;
    }
}
