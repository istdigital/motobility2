<?php

namespace StripeIntegration\Payments\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Backend\Model\Session;
use StripeIntegration\Payments\Model;
use Psr\Log\LoggerInterface;
use Magento\Framework\Validator\Exception;
use StripeIntegration\Payments\Helper\Logger;
use StripeIntegration\Payments\Model\PaymentMethod;
use StripeIntegration\Payments\Model\Config;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Store\Model\ScopeInterface;
use Magento\Authorization\Model\UserContextInterface;

class Generic
{
    public $magentoCustomerId = null;
    public $urlBuilder = null;
    protected $cards = [];
    public $orderComments = [];

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magento\Backend\Model\Session\Quote $backendSessionQuote,
        \Magento\Framework\App\Request\Http $request,
        LoggerInterface $logger,
        \Magento\Framework\App\State $appState,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\Order $order,
        \Magento\Sales\Model\Order\Invoice $invoice,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Sales\Model\Order\Creditmemo $creditmemo,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Sales\Block\Adminhtml\Order\Create\Form\Address $adminOrderAddressForm,
        \Magento\Customer\Model\CustomerRegistry $customerRegistry,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Sales\Api\Data\OrderInterfaceFactory $orderFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Sales\Model\Order\Invoice\CommentFactory $invoiceCommentFactory,
        \Magento\Customer\Model\Address $customerAddress,
        \Magento\Framework\Webapi\Response $apiResponse,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Framework\App\RequestInterface $requestInterface,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Sales\Model\Order\Email\Sender\OrderCommentSender $orderCommentSender,
        \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory,
        \Magento\Sales\Model\Service\CreditmemoService $creditmemoService
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->backendSessionQuote = $backendSessionQuote;
        $this->request = $request;
        $this->logger = $logger;
        $this->appState = $appState;
        $this->storeManager = $storeManager;
        $this->order = $order;
        $this->invoice = $invoice;
        $this->invoiceService = $invoiceService;
        $this->creditmemo = $creditmemo;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->resource = $resource;
        $this->coreRegistry = $coreRegistry;
        $this->adminOrderAddressForm = $adminOrderAddressForm;
        $this->customerRegistry = $customerRegistry;
        $this->messageManager = $messageManager;
        $this->productFactory = $productFactory;
        $this->quoteFactory = $quoteFactory;
        $this->orderFactory = $orderFactory;
        $this->cart = $cart;
        $this->invoiceCommentFactory = $invoiceCommentFactory;
        $this->customerAddress = $customerAddress;
        $this->apiResponse = $apiResponse;
        $this->transactionFactory = $transactionFactory;
        $this->requestInterface = $requestInterface;
        $this->urlBuilder = $urlBuilder;
        $this->pricingHelper = $pricingHelper;
        $this->cache = $cache;
        $this->encryptor = $encryptor;
        $this->userContext = $userContext;
        $this->orderSender = $orderSender;
        $this->priceCurrency = $priceCurrency;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->orderCommentSender = $orderCommentSender;
        $this->creditmemoFactory = $creditmemoFactory;
        $this->creditmemoService = $creditmemoService;
    }

    public function getBackendSessionQuote()
    {
        return $this->backendSessionQuote->getQuote();
    }

    public function isSecure()
    {
        return $this->request->isSecure();
    }

    public function getSessionQuote()
    {
        return $this->checkoutSession->getQuote();
    }

    public function getQuote()
    {
        // Admin area new order page
        if ($this->isAdmin())
            return $this->getBackendSessionQuote();

        // Front end checkout
        return $this->getSessionQuote();
    }

    public function getStoreId()
    {
        if ($this->isAdmin())
        {
            if ($this->request->getParam('order_id', null))
            {
                // Viewing an order
                $order = $this->order->load($this->request->getParam('order_id', null));
                return $order->getStoreId();
            }
            if ($this->request->getParam('invoice_id', null))
            {
                // Viewing an invoice
                $invoice = $this->invoice->load($this->request->getParam('invoice_id', null));
                return $invoice->getStoreId();
            }
            else if ($this->request->getParam('creditmemo_id', null))
            {
                // Viewing a credit memo
                $creditmemo = $this->creditmemo->load($this->request->getParam('creditmemo_id', null));
                return $creditmemo->getStoreId();
            }
            else
            {
                // Creating a new order
                $quote = $this->getBackendSessionQuote();
                return $quote->getStoreId();
            }
        }
        else
        {
            return $this->storeManager->getStore()->getId();
        }
    }

    public function loadProductById($productId)
    {
        $model = $this->productFactory->create();
        return $model->load($productId);
    }

    public function loadQuoteById($quoteId)
    {
        $model = $this->quoteFactory->create();
        return $model->load($quoteId);
    }

    public function loadOrderByIncrementId($incrementId)
    {
        return $this->orderFactory->create()->loadByIncrementId($incrementId);
    }

    public function loadOrderById($orderId)
    {
        return $this->orderFactory->create()->load($orderId);
    }

    public function loadCustomerById($customerId)
    {
        return $this->customerRepositoryInterface->getById($customerId);
    }

    public function createInvoiceComment($msg, $notify = false, $visibleOnFront = false)
    {
        return $this->invoiceCommentFactory->create()
            ->setComment($msg)
            ->setIsCustomerNotified($notify)
            ->setIsVisibleOnFront($visibleOnFront);
    }

    public function isAdmin()
    {
        $areaCode = $this->appState->getAreaCode();

        return $areaCode == \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE;
    }

    public function isCustomerLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    public function getCustomerId()
    {
        // If we are in the back office
        if ($this->isAdmin())
        {
            // About to refund/invoice an order
            if ($order = $this->coreRegistry->registry('current_order'))
                return $order->getCustomerId();

            // About to capture an invoice
            if ($invoice = $this->coreRegistry->registry('current_invoice'))
                return $invoice->getCustomerId();

            // Creating a new order from admin
            if ($this->adminOrderAddressForm && $this->adminOrderAddressForm->getCustomerId())
                return $this->adminOrderAddressForm->getCustomerId();
        }
        // If we are on the REST API
        else if ($this->userContext->getUserType() == UserContextInterface::USER_TYPE_CUSTOMER)
        {
            return $this->userContext->getUserId();
        }
        // If we are on the checkout page
        else if ($this->customerSession->isLoggedIn())
        {
            return $this->customerSession->getCustomerId();
        }
        // A webhook has instantiated this object
        else if (!empty($this->magentoCustomerId))
        {
            return $this->magentoCustomerId;
        }

        return null;
    }

    public function getMagentoCustomer()
    {
        if ($this->customerSession->getCustomer()->getEntityId())
            return $this->customerSession->getCustomer();

        $customerId = $this->getCustomerId();
        if (!$customerId) return;

        $customer = $this->customerRegistry->retrieve($customerId);

        if ($customer->getEntityId())
            return $customer;

        return null;
    }

    public function isGuest()
    {
        return !$this->customerSession->isLoggedIn();
    }

    // Should return the email address of guest customers
    public function getCustomerEmail()
    {
        $customer = $this->getMagentoCustomer();

        if (!$customer)
            $customer = $this->getGuestCustomer();

        if (!$customer)
            return null;

        return trim(strtolower($customer->getEmail()));
    }

    public function getGuestCustomer($order = null)
    {
        if ($order)
        {
            return $this->getAddressFrom($order, 'billing');
        }
        else if (isset($this->_order))
        {
            return $this->getAddressFrom($this->_order, 'billing');
        }
        else
            return null;
    }

    public function getCustomerDefaultBillingAddress()
    {
        $customer = $this->getMagentoCustomer();
        if (!$customer) return null;

        $addressId = $customer->getDefaultBilling();
        if (!$addressId) return null;

        $this->customerAddress->clearInstance();
        $address = $this->customerAddress->load($addressId);
        return $address;
    }

    public function getCustomerBillingAddress()
    {
        $quote = $this->getSessionQuote();
        if (empty($quote))
            return null;

        return $quote->getBillingAddress();
    }

    public function getStripeFormattedDefaultBillingAddress()
    {
        $address = $this->getCustomerDefaultBillingAddress();

        if (empty($address))
            return null;

        return [
            "address_line1" => $address->getStreetLine(1),
            "address_line2" => $address->getStreetLine(2),
            "address_city" => $address->getCity(),
            "address_state" => $address->getRegion(),
            "address_zip" => $address->getPostcode(),
            "address_country" => $address->getCountryId()
        ];
    }

    public function getStripeFormattedBillingAddress()
    {
        $address = $this->getCustomerBillingAddress();

        if (empty($address))
            return null;

        return [
            "address_line1" => $address->getStreetLine(1),
            "address_line2" => $address->getStreetLine(2),
            "address_city" => $address->getCity(),
            "address_state" => $address->getRegion(),
            "address_zip" => $address->getPostcode(),
            "address_country" => $address->getCountryId()
        ];
    }

    public function getCustomerLastRetrieved()
    {
        if (isset($this->customerLastRetrieved))
            return $this->customerLastRetrieved;

        // Get the magento customer id
        if (empty($customerId))
            $customerId = $this->getCustomerId();

        if (!empty($customerId) && $customerId < 1)
            $customerId = null;

        $connection = $this->resource->getConnection('core_read');
        $query = $connection->select()
            ->from('stripe_customers', ['*'])
            ->where('customer_id=?', $customerId);

        $result = $connection->fetchRow($query);
        if (empty($result)) return false;
        $this->customerStripeId = $result['stripe_id'];
        return $this->customerLastRetrieved = $result['last_retrieved'];
    }

    protected function updateLastRetrieved($stripeCustomerId)
    {
        try
        {
            $connection = $this->resource->getConnection('core_write');
            $fields = array();
            $fields['last_retrieved'] = time();
            $condition = array($connection->quoteInto('stripe_id=?', $stripeCustomerId));
            $result = $connection->update('stripe_customers', $fields, $condition);
        }
        catch (\Exception $e)
        {
            $this->logger->addError('Could not update Stripe customers table: '.$e->getMessage());
        }
    }

    public function getMultiCurrencyAmount($payment, $baseAmount)
    {
        $order = $payment->getOrder();
        $grandTotal = $order->getGrandTotal();
        $baseGrandTotal = $order->getBaseGrandTotal();

        $rate = $order->getBaseToOrderRate();
        if ($rate == 0) $rate = 1;

        // Full capture, ignore currency rate in case it changed
        if ($baseAmount == $baseGrandTotal)
            return $grandTotal;
        // Partial capture, consider currency rate but don't capture more than the original amount
        else if (is_numeric($rate))
            return min($baseAmount * $rate, $grandTotal);
        // Not a multicurrency capture
        else
            return $baseAmount;
    }

    public function getAddressFrom($order, $addressType = 'shipping')
    {
        if (!$order) return null;

        $addresses = $order->getAddresses();
        foreach ($addresses as $address)
        {
            if ($address["address_type"] == $addressType)
                return $address;
        }

        return null;
    }

    // Do not use Config::isSubscriptionsEnabled(), a circular dependency injection will appear
    public function isSubscriptionsEnabled()
    {
        $storeId = $this->getStoreId();

        $data = $this->scopeConfig->getValue("payment/stripe_payments_subscriptions/active", ScopeInterface::SCOPE_STORE, $storeId);

        return (bool)$data;
    }

    public function hasSubscriptionsIn($items)
    {
        if (!$this->isSubscriptionsEnabled())
            return false;

        foreach ($items as $item)
        {
            // Configurable products cannot be subscriptions. Also fixes a bug where if a configurable product
            // is added to the cart, and a bundled product already exists in the cart, Magento's core productModel->load()
            // method crashes with:
            // PHP Fatal error:  Uncaught Error: Call to undefined method Magento\Bundle\Model\Product\Type::getConfigurableAttributeCollection()
            if ($item->getProductType() == "configurable") continue;

            // The product has been deleted
            if (!$item->getProduct())
                continue;

            $product = $this->loadProductById($item->getProduct()->getEntityId());
            if ($product && $product->getStripeSubEnabled())
                return true;
        }

        return false;
    }

    public function hasSubscriptions()
    {
        if (isset($this->_hasSubscriptions) && $this->_hasSubscriptions)
            return true;

        $items = $this->cart->getQuote()->getAllItems();
        return $this->_hasSubscriptions = $this->hasSubscriptionsIn($items);
    }

    public function isZeroDecimal($currency)
    {
        return in_array(strtolower($currency), array(
            'bif', 'djf', 'jpy', 'krw', 'pyg', 'vnd', 'xaf',
            'xpf', 'clp', 'gnf', 'kmf', 'mga', 'rwf', 'vuv', 'xof'));
    }

    public function isAuthorizationExpired($errorMessage)
    {
        return ((strstr($errorMessage, "cannot be captured because the charge has expired") !== false) ||
            (strstr($errorMessage, "could not be captured because it has a status of canceled") !== false));
    }

    public function addError($msg)
    {
        $this->messageManager->addError( __($msg) );
    }

    public function addSuccess($msg)
    {
        $this->messageManager->addSuccess( __($msg) );
    }

    public function logError($msg)
    {
        $this->logger->addError(Config::module() . ": " . $msg);
    }

    public function isAjaxRequest()
    {
        return $this->requestInterface->isXmlHttpRequest();
    }

    public function isStripeAPIKeyError($msg)
    {
        $pos1 = stripos($msg, "Invalid API key provided");
        $pos2 = stripos($msg, "No API key provided");
        if ($pos1 !== false || $pos2 !== false)
            return true;

        return false;
    }

    public function cleanError($msg)
    {
        if ($this->isStripeAPIKeyError($msg))
            return "Invalid Stripe API key provided.";

        return $msg;
    }

    public function isMultiShipping()
    {
        $quote = $this->getSessionQuote();
        if (empty($quote))
            return false;

        return $quote->getIsMultiShipping();
    }

    public function dieWithError($msg, $e = null)
    {
        $this->logError($msg);

        if ($e)
        {
            if ($e->getMessage() != $msg)
                $this->logError($e->getMessage());

            $this->logError($e->getTraceAsString());
        }

        if ($this->isAdmin())
            throw new CouldNotSaveException(__($msg));
        else if ($this->isAjaxRequest())
            throw new CouldNotSaveException(__($this->cleanError($msg)), $e);
        else if ($this->isMultiShipping())
            throw new \Magento\Framework\Exception\LocalizedException(__($msg), $e);
        else
            $this->addError($this->cleanError($msg));
    }

    public function maskException($e)
    {
        if (strpos($e->getMessage(), "Received unknown parameter: payment_method_options[card][moto]") === 0)
            throw new CouldNotSaveException(__("You have enabled MOTO exemptions from the Stripe module configuration section, but your Stripe account has not been gated to use MOTO exemptions. Please contact support@stripe.com to request MOTO enabled for your Stripe account."));

        throw $e;
    }

    public function isValidToken($token)
    {
        if (!is_string($token))
            return false;

        if (!strlen($token))
            return false;

        if (strpos($token, "_") === FALSE)
            return false;

        return true;
    }

    public function captureOrder($order)
    {
        foreach($order->getInvoiceCollection() as $invoice)
        {
            $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
            $invoice->capture();
            $invoice->save();
        }
    }

    public function getInvoiceAmounts($invoice, $details)
    {
        $currency = strtolower($details['currency']);
        $cents = 100;
        if ($this->isZeroDecimal($currency))
            $cents = 1;
        $amount = ($details['amount'] / $cents);
        $baseAmount = round($amount * $invoice->getBaseToOrderRate(), 2);

        if (!empty($details["shipping"]))
        {
            $shipping = ($details['shipping'] / $cents);
            $baseShipping = round($shipping * $invoice->getBaseToOrderRate(), 2);
        }
        else
        {
            $shipping = 0;
            $baseShipping = 0;
        }

        if (!empty($details["tax"]))
        {
            $tax = ($details['tax'] / $cents);
            $baseTax = round($tax * $invoice->getBaseToOrderRate(), 2);
        }
        else
        {
            $tax = 0;
            $baseTax = 0;
        }

        return [
            "amount" => $amount,
            "base_amount" => $baseAmount,
            "shipping" => $shipping,
            "base_shipping" => $baseShipping,
            "tax" => $tax,
            "base_tax" => $baseTax
        ];
    }

    // Used for partial invoicing triggered from a partial Stripe dashboard capture
    public function adjustInvoiceAmounts(&$invoice, $details)
    {
        if (!is_array($details))
            return;

        $amounts = $this->getInvoiceAmounts($invoice, $details);
        $amount = $amounts['amount'];
        $baseAmount = $amounts['base_amount'];

        if ($invoice->getGrandTotal() != $amount)
        {
            $invoice->setShippingAmount($amounts['shipping']);
            $invoice->setBaseShippingAmount($amounts['base_shipping']);
            $invoice->setSubtotal($invoice->getSubtotalInclTax() - $amounts['tax']);
            $invoice->setBaseSubtotal($invoice->getBaseSubtotalInclTax() - $amounts['base_tax']);
            $invoice->setTaxAmount($amounts['tax']);
            $invoice->setBaseTaxAmount($amounts['base_tax']);
            $invoice->setGrandTotal($amount);
            $invoice->setBaseGrandTotal($baseAmount);
        }
    }

    public function invoiceSubscriptionOrder($order, $transactionId = null, $captureCase = \Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE, $amount = null, $save = true)
    {
        if ($save)
            $dbTransaction = $this->transactionFactory->create();

        $invoice = $this->invoiceService->prepareInvoice($order);
        $invoice->setRequestedCaptureCase($captureCase);

        if ($transactionId)
        {
            $invoice->setTransactionId($transactionId);
            $order->getPayment()->setLastTransId($transactionId);
        }

        $this->adjustInvoiceAmounts($invoice, $amount);

        $invoice->register();

        if ($save)
            $dbTransaction->addObject($invoice)
                    ->addObject($order)
                    ->save();

        return $invoice;
    }

    public function invoiceOrder($order, $transactionId = null, $captureCase = \Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE, $amount = null, $save = true)
    {
        if ($save)
            $dbTransaction = $this->transactionFactory->create();

        // This will kick in with "Authorize Only" mode orders, but not with "Authorize & Capture"
        if ($order->canInvoice())
        {
            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->setRequestedCaptureCase($captureCase);

            if ($transactionId)
            {
                $invoice->setTransactionId($transactionId);
                $order->getPayment()->setLastTransId($transactionId);
            }

            $this->adjustInvoiceAmounts($invoice, $amount);

            $invoice->register();

            if ($save)
                $dbTransaction->addObject($invoice)
                        ->addObject($order)
                        ->save();

            return $invoice;
        }
        // Invoices have already been generated with either Authorize Only or Authorize & Capture, but have not actually been captured because
        // the source is not chargeable yet. These should have a pending status.
        else
        {
            foreach($order->getInvoiceCollection() as $invoice)
            {
                if ($invoice->canCapture())
                {
                    $invoice->setRequestedCaptureCase($captureCase);

                    $this->adjustInvoiceAmounts($invoice, $amount);

                    $invoice->pay();

                    if ($save)
                        $dbTransaction->addObject($invoice)
                                ->addObject($order)
                                ->save();

                    return $invoice;
                }
            }
        }

        return null;
    }

    // Pending orders are the ones that were placed with an asynchronous payment method, such as SOFORT or SEPA Direct Debit,
    // which may finalize the charge after several days or weeks
    public function invoicePendingOrder($order, $transactionId = null, $amount = null)
    {
        if (!$order->canInvoice())
            throw new \Exception("Order #" . $order->getIncrementId() . " cannot be invoiced.");

        $invoice = $this->invoiceService->prepareInvoice($order);

        if ($transactionId)
        {
            $captureCase = \Magento\Sales\Model\Order\Invoice::NOT_CAPTURE;
            $invoice->setTransactionId($transactionId);
            $order->getPayment()->setLastTransId($transactionId);
        }
        else
        {
            $captureCase = \Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE;
        }

        $invoice->setRequestedCaptureCase($captureCase);

        $this->adjustInvoiceAmounts($invoice, $amount);

        $invoice->register();

        $dbTransaction = $this->transactionFactory->create();

        $dbTransaction
            ->addObject($invoice)
            ->addObject($order)
            ->save();

        return $invoice;
    }

    public function cancelOrCloseOrder($order, $refundInvoices = false, $refundOffline = true)
    {
        $cancelled = false;

        $dbTransaction = $this->transactionFactory->create();

        // When in Authorize & Capture, uncaptured invoices exist, so we should cancel them first
        foreach($order->getInvoiceCollection() as $invoice)
        {
            if ($invoice->canCancel())
            {
                $invoice->cancel();
                $dbTransaction->addObject($invoice);
                $cancelled = true;
            }
            else if ($refundInvoices)
            {
                $creditmemo = $this->creditmemoFactory->createByOrder($order);
                $creditmemo->setInvoice($invoice);
                $this->creditmemoService->refund($creditmemo, $refundOffline);
                $cancelled = true;
            }
        }

        // When all invoices have been canceled, the order can be canceled
        if ($order->canCancel())
        {
            $order->cancel();
            $dbTransaction->addObject($order);
            $cancelled = true;
        }

        $dbTransaction->save();

        return $cancelled;
    }

    public function getSanitizedBillingInfo()
    {
        // This method is unnecessary in M2, the checkout passes the correct billing details
    }

    public function retrieveSource($token)
    {
        if (isset($this->sources[$token]))
            return $this->sources[$token];

        $this->sources[$token] = \Stripe\Source::retrieve($token);

        return $this->sources[$token];
    }

    public function maskError($msg)
    {
        if (stripos($msg, "You must verify a phone number on your Stripe account") === 0)
            return $msg;

        return false;
    }

    // Removes decorative strings that Magento adds to the transaction ID
    public function cleanToken($token)
    {
        return preg_replace('/-.*$/', '', $token);
    }

    public function retrieveCard($customer, $token)
    {
        if (isset($this->cards[$token]))
            return $this->cards[$token];

        $card = $customer->sources->retrieve($token);
        $this->cards[$token] = $card;

        return $card;
    }

    public function convertPaymentMethodToCard($paymentMethod)
    {
        if (!$paymentMethod || empty($paymentMethod->card))
            return null;

        $card = json_decode(json_encode($paymentMethod->card));
        $card->id = $paymentMethod->id;

        return $card;
    }

    public function cardType($code)
    {
        switch ($code) {
            case 'visa': return "Visa";
            case 'amex': return "American Express";
            case 'mastercard': return "MasterCard";
            case 'discover': return "Discover";
            case 'diners': return "Diners Club";
            case 'jcb': return "JCB";
            case 'unionpay': return "UnionPay";
            default:
                return ucfirst($code);
        }
    }

    public function listCards($customer, $params = array())
    {
        try
        {
            $sources = $customer->sources;
            if (!empty($sources))
            {
                $cards = [];

                // Cards created through the Payment Methods API
                $data = \Stripe\PaymentMethod::all(['customer' => $customer->id, 'type' => 'card', 'limit' => 100]);
                foreach ($data->autoPagingIterator() as $pm)
                {
                    $cards[] = $this->convertPaymentMethodToCard($pm);
                }

                return $cards;
            }
            else
                return null;
        }
        catch (\Exception $e)
        {
            return null;
        }
    }

    public function findCard($customer, $last4, $expMonth, $expYear)
    {
        $cards = $this->listCards($customer);
        foreach ($cards as $card)
        {
            if ($last4 == $card->last4 &&
                $expMonth == $card->exp_month &&
                $expYear == $card->exp_year)
            {
                return $card;
            }
        }

        return false;
    }

    public function findCardByFingerprint($customer, $fingerprint)
    {
        $cards = $this->listCards($customer);
        foreach ($cards as $card)
        {
            if ($card->fingerprint == $fingerprint)
            {
                return $card;
            }
        }

        return false;
    }

    public function findSubscriptionsUsingPaymentMethod($paymentMethodId, $customer)
    {
        $subscriptions = [];

        if (empty($customer->subscriptions->data))
            return [];

        foreach ($customer->subscriptions->data as $subscription)
        {
            if ($subscription->default_payment_method == $paymentMethodId)
                $subscriptions[] = $subscription;
        }

        return $subscriptions;
    }

    public function addSavedCard($customer, $newcard)
    {
        if (!$customer)
            return;

        if (!is_string($newcard))
            return null;

        // If we are adding a payment method, called from My Saved Cards section
        if (strpos($newcard, 'pm_') === 0)
        {
            $pm = \Stripe\PaymentMethod::retrieve($newcard);

            if (!isset($pm->card->fingerprint))
                return null;

            $card = $this->findCardByFingerprint($customer, $pm->card->fingerprint);

            $subscriptions = [];
            if ($card)
            {
                $subscriptions = $this->findSubscriptionsUsingPaymentMethod($card->id, $customer);
                \StripeIntegration\Payments\Model\Config::$stripeClient->paymentMethods->detach($card->id);
            }

            $pm->attach([ 'customer' => $customer->id ]);

            foreach ($subscriptions as $subscription)
                \StripeIntegration\Payments\Model\Config::$stripeClient->subscriptions->update($subscription->id, ['default_payment_method' => $pm->id]);

            return $this->convertPaymentMethodToCard($pm);
        }
        // If we are adding a source
        else if (strpos($newcard, 'src_') === 0)
        {
            $source = $this->retrieveSource($newcard);
            // Card sources have been depreciated, we can only add Payment Method tokens pm_
            // if ($source->type == 'card')
            // {
            //     $card = $this->convertSourceToCard($source);
            // }
            if ($source->usage == 'reusable' && !isset($source->amount))
            {
                // SEPA Direct Debit with no amount set, no deduplication here
                $card = $customer->sources->create(array('source' => $source->id));
                $customer->default_source = $card->id;
                $customer->save();
                return $card;
            }
            else
            {
                // Bancontact, iDEAL etc cannot be added because they are not reusable
                return null;
            }

            if (isset($card->last4))
            {
                $last4 = $card->last4;
                $month = $card->exp_month;
                $year = $card->exp_year;
                $exists = $this->findCard($customer, $last4, $month, $year);
                if ($exists)
                {
                    $customer->default_source = $exists->id;
                    $customer->save();
                    return $exists;
                }
                else
                {
                    $card2 = $customer->sources->create(array('source' => $card->id));
                    $customer->default_source = $card2->id;
                    $customer->save();
                    return $card2;
                }
            }
        }

        return null;
    }

    public function formatStripePrice($price, $currency = null)
    {
        if (!$this->isZeroDecimal($currency))
            $price /= 100;

        return $this->pricingHelper->currency($price, true, false);
    }

    public function getRefundIdFrom($charge)
    {
        $lastRefundDate = 0;
        $refundId = null;

        foreach ($charge->refunds->data as $refund)
        {
            // There might be multiple refunds, and we are looking for the most recent one
            if ($refund->created > $lastRefundDate)
            {
                $lastRefundDate = $refund->created;
                $refundId = $refund->id;
            }
        }

        return $refundId;
    }

    public function convertBaseAmountToStoreAmount($baseAmount)
    {
        $store = $this->storeManager->getStore();
        return $store->getBaseCurrency()->convert($baseAmount, $store->getCurrentCurrencyCode());
    }

    public function getUrl($path)
    {
        return $this->urlBuilder->getUrl($path, ['_secure' => $this->request->isSecure()]);
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getStripeFormattedBillingAddressFrom($quote)
    {
        $billingAddress = $quote->getBillingAddress();
        $street = $billingAddress->getStreet();
        $line2 = (isset($street[1]) ? $street[1] : null);
        return [
            "address" => [
                "city" => $billingAddress->getCity(),
                "line1" => $street[0],
                "line2" => $line2,
                "country" => $billingAddress->getCountryId(),
                "postal_code" => $billingAddress->getPostcode(),
                "state" => $billingAddress->getRegion()
            ],
            "phone" => $billingAddress->getTelephone(),
            "name" => $billingAddress->getName(),
            "email" => $quote->getCustomerEmail()
        ];
    }

    public function updateBillingAddress($token)
    {
        if (strpos($token, "pm_") === 0)
        {
            $paymentMethod = \Stripe\PaymentMethod::retrieve($token);
            $quote = $this->getQuote();
            $magentoBillingDetails = $this->getStripeFormattedBillingAddressFrom($quote);
            $paymentMethodBillingDetails = [
                "address" => [
                    "city" => $paymentMethod->billing_details->address->city,
                    "line1" => $paymentMethod->billing_details->address->line1,
                    "line2" => $paymentMethod->billing_details->address->line2,
                    "country" => $paymentMethod->billing_details->address->country,
                    "postal_code" => $paymentMethod->billing_details->address->postal_code,
                    "state" => $paymentMethod->billing_details->address->state
                ],
                "phone" => $paymentMethod->billing_details->phone,
                "name" => $paymentMethod->billing_details->name,
                "email" => $paymentMethod->billing_details->email
            ];
            if ($paymentMethodBillingDetails != $magentoBillingDetails || $paymentMethodBillingDetails["address"] != $magentoBillingDetails["address"])
            {
                \Stripe\PaymentMethod::update(
                  $paymentMethod->id,
                  ['billing_details' => $magentoBillingDetails]
                );
            }
        }
    }

    public function sendNewOrderEmailFor($order)
    {
        // Send the order email
        $this->orderSender->send($order);

        // if ($order->getCanSendNewEmailFlag())
        // {
        //     try {
        //         $order->sendNewOrderEmail();
        //     } catch (\Exception $e) {
        //         $this->log($e->getMessage());
        //     }
        // }
    }

    // An assumption is made that Webhooks->initStripeFrom($order) has already been called
    // to set the store and currency before the conversion, as the pricingHelper uses those
    public function getFormattedStripeAmount($amount, $currency, $order)
    {
        $orderAmount = $this->convertStripeAmountToOrderAmount($amount, $currency, $order);

        return $this->addCurrencySymbol($orderAmount, $currency);
    }

    public function convertStripeAmountToBaseOrderAmount($amount, $currency, $order)
    {
        if (strtolower($currency) != strtolower($order->getOrderCurrencyCode()))
            throw new \Exception("The order currency does not match the Stripe currency");

        $cents = 100;

        if ($this->isZeroDecimal($currency))
            $cents = 1;

        $amount = ($amount / $cents);
        $baseAmount = round($amount / $order->getBaseToOrderRate(), 2);

        return $baseAmount;
    }

    public function convertStripeAmountToOrderAmount($amount, $currency, $order)
    {
        if (strtolower($currency) != strtolower($order->getOrderCurrencyCode()))
            throw new \Exception("The order currency does not match the Stripe currency");

        $cents = 100;

        if ($this->isZeroDecimal($currency))
            $cents = 1;

        $amount = ($amount / $cents);

        return $amount;
    }

    public function addCurrencySymbol($amount, $currencyCode = null)
    {
        if (empty($currencyCode))
            $currencyCode = $this->storeManager->getStore()->getCurrentCurrency()->getCode();

        return $this->priceCurrency->format($amount, false, null, null, strtoupper($currencyCode));
    }

    public function getSubscriptionProductIdFrom($item)
    {
        $type = $item->getProductType();
        switch ($type) {
            case 'configurable':
                foreach ($item->getChildrenItems() as $child)
                    return $child->getProduct()->getId();
            default:
                return $item->getProductId();
        }
    }

    public function getSubscriptionProductFrom($item)
    {
        $productId = $this->getSubscriptionProductIdFrom($item);
        return $this->loadProductById($productId);
    }

    public function getClearSourceInfo($data)
    {
        $info = [];
        $remove = ['mandate_url', 'fingerprint', 'client_token'];
        foreach ($data as $key => $value)
        {
            if (!in_array($key, $remove))
                $info[$key] = $value;
        }

        // Remove Klarna pay fields
        $startsWith = ["pay_"];
        foreach ($info as $key => $value)
        {
            foreach ($startsWith as $part)
            {
                if (strpos($key, $part) === 0)
                    unset($info[$key]);
            }
        }

        return $info;
    }

    public function notifyCustomer($order, $comment)
    {
        $order->addStatusToHistory($status = false, $comment, $isCustomerNotified = true);
        $order->setCustomerNote($comment);
        // $order->save();
        $this->orderCommentSender->send($order, $notify = true, $comment);
    }

    public function sendNewOrderEmailWithComment($order, $comment)
    {
        $order->addStatusToHistory($status = false, $comment, $isCustomerNotified = true);
        $this->orderComments[$order->getIncrementId()] = $comment;
        $order->setEmailSent(false);
        $this->orderSender->send($order, true);
    }

    public function isAuthenticationRequiredMessage($message)
    {
        if (strpos($message, "Authentication Required: ") === 0)
            return true;

        return false;
    }
}
