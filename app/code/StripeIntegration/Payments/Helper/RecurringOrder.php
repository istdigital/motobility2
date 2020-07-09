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
    public $quoteManagement = null;

    public function __construct(
        \StripeIntegration\Payments\Helper\Generic $paymentsHelper,
        \StripeIntegration\Payments\Model\Config $config,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Store\Model\Store $storeManager,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
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
        $this->quoteManagement = $quoteManagement;
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
        $subscription = $this->getSubscriptionFrom($invoice);
        $subscriptionAmount = $this->convertToMagentoAmount($subscription->amount / $subscription->quantity, $invoice->currency);
        $baseSubscriptionAmount = round($subscriptionAmount / $order->getBaseToOrderRate(), 2);

        $details = [
            "invoice_amount" => $this->convertToMagentoAmount($invoice->amount_paid, $invoice->currency),
            "base_invoice_amount" => round($this->convertToMagentoAmount($invoice->amount_paid, $invoice->currency) / $order->getBaseToOrderRate(), 2),
            "invoice_currency" => $invoice->currency,
            "invoice_tax_percent" => $invoice->tax_percent,
            "invoice_tax_amount" => $this->convertToMagentoAmount($invoice->tax, $invoice->currency),
            "subscription_amount" => $subscriptionAmount,
            "base_subscription_amount" => $baseSubscriptionAmount,
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
            "discount_amount" => $this->getDiscountAmountFrom($invoice),
            "discount_coupon" => $order->getCouponCode(),
            "products" => [],
            "shipping_address" => [],
            "charge_id" => $invoice->charge
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

    public function getDiscountAmountFrom($invoice)
    {
        if (empty($invoice->data->object->discount->coupon->amount_off))
            return 0;

        return $this->convertToMagentoAmount($invoice->data->object->discount->coupon->amount_off, $invoice->currency);
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
        $quote = $this->createQuoteFrom($originalOrder);
        $this->setQuoteCustomerFrom($originalOrder, $quote);
        $this->setQuoteAddressesFrom($originalOrder, $quote);
        $this->setQuoteItemsFrom($originalOrder, $invoiceDetails, $quote);
        $this->setQuoteShippingMethodFrom($originalOrder, $quote);
        $this->setQuoteDiscountFrom($originalOrder, $quote);
        $this->setQuotePaymentMethodFrom($originalOrder, $quote);

        // Collect Totals & Save Quote
        $quote->collectTotals()->save();

        // Create Order From Quote
        $order = $this->quoteManagement->submit($quote);
        $this->addOrderCommentsTo($order, $originalOrder);
        $this->setTransactionDetailsFor($order, $invoiceDetails);

        return $order;
    }

    public function addOrderCommentsTo($order, $originalOrder)
    {
        $subscriptionId = $this->invoice->subscription->id;
        $orderIncrementId = $originalOrder->getIncrementId();
        $comment = "Recurring order generated from subscription with ID $subscriptionId. ";
        $comment .= "Customer originally subscribed with order #$orderIncrementId. ";
        $order->setEmailSent(0);
        $order->addStatusToHistory('processing', $comment, false)->save();
    }

    public function setTransactionDetailsFor($order, $invoiceDetails)
    {
        $transactionId = $invoiceDetails["charge_id"];

        $order->getPayment()
            ->setLastTransId($transactionId)
            ->setIsTransactionClosed(0)
            ->save();

        foreach($order->getInvoiceCollection() as $invoice)
            $invoice->setTransactionId($transactionId)->save();
    }

    public function setQuoteDiscountFrom($originalOrder, &$quote)
    {
        if (!empty($originalOrder->getCouponCode()))
            $quote->setCouponCode($originalOrder->getCouponCode());
    }

    public function setQuotePaymentMethodFrom($originalOrder, &$quote, $data = [])
    {
        $quote->setPaymentMethod($originalOrder->getPayment()->getMethod());
        $quote->setInventoryProcessed(false);
        $quote->save(); // Needed before setting payment data
        $data = array_merge($data, ['method' => $originalOrder->getPayment()->getMethod()]);
        $quote->getPayment()
            ->importData($data)
            ->setAdditionalInformation("is_recurring_subscription", true);
    }

    public function setQuoteShippingMethodFrom($originalOrder, &$quote)
    {
        if (!$originalOrder->getIsVirtual())
        {
            $quote->getShippingAddress()
                ->setCollectShippingRates(true)
                ->collectShippingRates()
                ->setShippingMethod($originalOrder->getShippingMethod());
        }
    }

    public function setQuoteItemsFrom($originalOrder, $invoiceDetails, &$quote)
    {
        foreach ($invoiceDetails['products'] as $productId => $product)
        {
            $productModel = $this->paymentsHelper->loadProductById($productId);
            $quoteItem = $quote->addProduct($productModel, $product['qty']);

            if ($invoiceDetails['base_subscription_amount'] != $productModel->getPrice())
            {
                $quoteItem->setCustomPrice($invoiceDetails['subscription_amount']);
                $quoteItem->setOriginalCustomPrice($invoiceDetails['subscription_amount']);

                // @todo - Magento bug where the base price is not calculated when a custom price is set, causing a wrong tax calculation
                // https://github.com/magento/magento2/issues/28462
                // $quoteItem->setBaseCustomPrice($invoiceDetails['base_subscription_amount']);
                // $quoteItem->setBaseOriginalCustomPrice($invoiceDetails['base_subscription_amount']);
            }
        }
    }

    public function setQuoteAddressesFrom($originalOrder, &$quote)
    {
        if ($originalOrder->getIsVirtual())
        {
            $data = $this->filterAddressData($originalOrder->getBillingAddress()->getData());
            $quote->getBillingAddress()->addData($data);
            $quote->setIsVirtual(true);
        }
        else
        {
            $data = $this->filterAddressData($originalOrder->getBillingAddress()->getData());
            $quote->getBillingAddress()->addData($data);

            $data = $this->filterAddressData($originalOrder->getShippingAddress()->getData());
            $quote->getShippingAddress()->addData($originalOrder->getShippingAddress()->getData());
        }
    }

    public function filterAddressData($data)
    {
        $allowed = ['prefix', 'firstname', 'middlename', 'lastname', 'email', 'suffix', 'company', 'street', 'city', 'country_id', 'region', 'region_id', 'postcode', 'telephone', 'fax', 'vat_id'];
        $remove = [];

        foreach ($data as $key => $value)
            if (!in_array($key, $allowed))
                $remove[] = $key;

        foreach ($remove as $key)
            unset($data[$key]);

        return $data;
    }

    public function createQuoteFrom($originalOrder)
    {
        $store = $this->storeManager->load($originalOrder->getStoreId());
        $store->setCurrentCurrencyCode($originalOrder->getOrderCurrencyCode());

        $quote = $this->quoteFactory->create();
        $quote->setStore($store);
        $quote->setStoreId($store->getId());
        $quote->setQuoteCurrencyCode($originalOrder->getOrderCurrencyCode());
        $quote->setCustomerEmail($originalOrder->getCustomerEmail());
        $quote->setIsRecurringOrder(true);

        return $quote;
    }

    public function setQuoteCustomerFrom($originalOrder, &$quote)
    {

        if ($originalOrder->getCustomerIsGuest())
        {
            $quote->setCustomerIsGuest(true);
        }
        else
        {
            $customer = $this->paymentsHelper->loadCustomerById($originalOrder->getCustomerId());
            $quote->assignCustomer($customer);
        }
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

    public function getSubscriptionFrom($invoice)
    {
        foreach ($invoice->lines->data as $lineItem)
            if ($lineItem->type == "subscription")
                return $lineItem;

        return null;
    }
}
