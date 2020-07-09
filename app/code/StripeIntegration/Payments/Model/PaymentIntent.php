<?php

namespace StripeIntegration\Payments\Model;

use Magento\Framework\Validator\Exception;
use Magento\Framework\Exception\LocalizedException;
use StripeIntegration\Payments\Helper\Logger;

class PaymentIntent
{
    public $paymentIntent = null;
    public $paymentIntentsCache = [];
    public $params = [];
    public $stopUpdatesForThisSession = false;
    public $quote = null; // Overwrites default quote
    public $order = null;
    public $capture = null; // Overwrites default capture method

    const CAPTURED = "succeeded";
    const AUTHORIZED = "requires_capture";
    const CAPTURE_METHOD_MANUAL = "manual";
    const CAPTURE_METHOD_AUTOMATIC = "automatic";
    const REQUIRES_ACTION = "requires_action";
    const CANCELED = "canceled";

    public function __construct(
        \StripeIntegration\Payments\Helper\Generic $helper,
        \StripeIntegration\Payments\Helper\Rollback $rollback,
        \StripeIntegration\Payments\Helper\Subscriptions $subscriptionsHelper,
        \Magento\Framework\App\CacheInterface $cache,
        \StripeIntegration\Payments\Model\Config $config,
        \StripeIntegration\Payments\Model\StripeCustomer $customer,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Session\Generic $session,
        \Magento\Checkout\Helper\Data $checkoutHelper
        )
    {
        $this->helper = $helper;
        $this->rollback = $rollback;
        $this->subscriptionsHelper = $subscriptionsHelper;
        $this->cache = $cache;
        $this->config = $config;
        $this->customer = $customer;
        $this->quoteFactory = $quoteFactory;
        $this->quoteRepository = $quoteRepository;
        $this->addressFactory = $addressFactory;
        $this->eventManager = $eventManager;
        $this->session = $session;
        $this->checkoutHelper = $checkoutHelper;
    }

    // If we already created any payment intents for this quote, load them
    public function loadFromCache($quote)
    {
        if (empty($quote))
            return null;

        $quoteId = $quote->getId();

        if (empty($quoteId))
            $quoteId = $quote->getQuoteId(); // Admin order quotes

        if (empty($quoteId))
            return null;

        $key = 'payment_intent_' . $quoteId;
        $paymentIntentId = $this->session->getData($key);
        if (!empty($paymentIntentId) && strpos($paymentIntentId, "pi_") === 0)
        {
            if (isset($this->paymentIntentsCache[$paymentIntentId]) && $this->paymentIntentsCache[$paymentIntentId] instanceof \Stripe\PaymentIntent)
                $this->paymentIntent = $this->paymentIntentsCache[$paymentIntentId];
            else
            {
                $this->paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);
                $this->updateCache($quoteId);
            }
        }
        else
            return null;

        if ($this->isInvalid($quote) || $this->hasPaymentActionChanged())
        {
            $this->destroy($quoteId, true);
            return null;
        }

        return $this->paymentIntent;
    }

    public function loadFromPayment($payment)
    {
        if (empty($payment))
            throw new LocalizedException("Unhandled attempt to place multi-shipping order without a payment object");

        $paymentIntentId = $payment->getAdditionalInformation("payment_intent_id");

        if (empty($paymentIntentId))
        {
            $this->paymentIntent = null;
            return null;
        }

        try
        {
            $this->paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);
            $this->updateCache($paymentIntentId); // We sent a $paymentIntentId and not a $quoteId intentionally!
            return $this->paymentIntent;
        }
        catch (\Exception $e)
        {
            $this->paymentIntent = null;
            return null;
        }
    }

    protected function hasPaymentActionChanged()
    {
        $captureMethod = $this->getCaptureMethod();
        return ($captureMethod != $this->paymentIntent->capture_method);
    }

    public function create($quote = null, $payment = null)
    {
        if (!$this->config->isEnabled())
            return null;

        if (empty($quote))
            $quote = $this->getQuote();

        // We don't want to be creating a payment intent if there is no cart/quote
        if (!$quote)
        {
            $this->paymentIntent = null;
            return null;
        }

        $this->getParamsFrom($quote, $payment);

        if ($this->helper->isMultiShipping())
            $this->loadFromPayment($payment);
        else
            $this->loadFromCache($quote);

        if (empty($this->params['amount']) || $this->params['amount'] == 0)
            return null;

        if (!$this->paymentIntent)
        {
            $this->paymentIntent = \Stripe\PaymentIntent::create($this->params);
            $this->updateCache($quote->getId());

            if ($payment)
            {
                $payment->setAdditionalInformation("payment_intent_id", $this->paymentIntent->id);
                $payment->setAdditionalInformation("payment_intent_client_secret", $this->paymentIntent->client_secret);
            }
        }
        else if ($this->differentFrom($quote))
        {
            $this->updateFrom($quote);
        }

        return $this;
    }

    protected function updateCache($quoteId)
    {
        $key = 'payment_intent_' . $quoteId;
        $data = $this->paymentIntent->id;
        $this->session->setData($key, $data);
        $this->paymentIntentsCache[$this->paymentIntent->id] = $this->paymentIntent;
    }

    protected function getParamsFrom($quote, $payment = null)
    {
        if ($this->config->useStoreCurrency())
        {
            if ($this->helper->isMultiShipping())
                $amount = $payment->getOrder()->getGrandTotal();
            else
                $amount = $quote->getGrandTotal();

            $currency = $quote->getQuoteCurrencyCode();
        }
        else
        {
            if ($this->helper->isMultiShipping())
                $amount = $payment->getOrder()->getBaseGrandTotal();
            else
                $amount = $quote->getBaseGrandTotal();

            $currency = $quote->getBaseCurrencyCode();
        }

        $cents = 100;
        if ($this->helper->isZeroDecimal($currency))
            $cents = 1;

        $this->params['amount'] = round($amount * $cents);
        $this->params['currency'] = strtolower($currency);
        $this->params['capture_method'] = $this->getCaptureMethod();
        $this->params["payment_method_types"] = ["card"]; // For now
        $this->params['confirmation_method'] = 'manual';

        $this->adjustAmountForSubscriptions();

        $statementDescriptor = $this->config->getStatementDescriptor();
        if (!empty($statementDescriptor))
            $this->params["statement_descriptor"] = $statementDescriptor;
        else
            unset($this->params['statement_descriptor']);

        $shipping = $this->getShippingAddressFrom($quote);
        if ($shipping)
            $this->params['shipping'] = $shipping;
        else
            unset($this->params['shipping']);

        return $this->params;
    }

    // Adds initial fees, or removes item amounts if there is a trial set
    protected function adjustAmountForSubscriptions()
    {
        $amount = $this->params["amount"];
        $cents = 100;
        if ($this->helper->isZeroDecimal($this->params['currency']))
            $cents = 1;

        $data = $this->subscriptionsHelper->createSubscriptions($this->getQuote(), true);

        if (!empty($data['error']))
            throw new LocalizedException($data['error']);

        $this->params["amount"] = round((($amount/$cents) - $data['subscriptionsTotal']) * $cents);
    }

    // Returns true if we have already created a paymentIntent with these parameters
    protected function alreadyCreated($amount, $currency, $methods)
    {
        return (!empty($this->paymentIntent) &&
            $this->paymentIntent->amount == $amount &&
            $this->paymentIntent->currency == $currency &&
            $this->samePaymentMethods($methods)
            );
    }

    // Checks if the payment methods in the parameter are the same with the payment methods on $this->paymentMethods
    protected function samePaymentMethods($methods)
    {
        $currentMethods = $this->paymentIntent->payment_method_types;
        return (empty(array_diff($methods, $currentMethods)) &&
            empty(array_diff($currentMethods, $methods)));
    }

    public function getClientSecret()
    {
        if (empty($this->paymentIntent))
            return null;

        if (!$this->config->isEnabled())
            return null;

        return $this->paymentIntent->client_secret;
    }

    public function getStatus()
    {
        if (empty($this->paymentIntent))
            return null;

        if (!$this->config->isEnabled())
            return null;

        return $this->paymentIntent->status;
    }

    public function getPaymentIntentID()
    {
        if (empty($this->paymentIntent))
            return null;

        return $this->paymentIntent->id;
    }

    protected function getQuote()
    {
        // Capturing an expired authorization
        if ($this->quote)
            return $this->quote;

        // Admin area new order page
        if ($this->helper->isAdmin())
        {
            $quoteId = $this->helper->getBackendSessionQuote()->getQuoteId();
            $quote = $this->quoteRepository->get($quoteId);
            return $quote;
        }

        // Front end checkout
        return $this->helper->getSessionQuote();
    }

    public function isInvalid($quote)
    {
        if (!isset($this->params['amount']))
            $this->getParamsFrom($quote);

        if ($this->params['amount'] <= 0)
            return true;

        if ($this->paymentIntent->status == $this::CANCELED)
            return true;
        else if ($this->paymentIntent->status == $this::REQUIRES_ACTION)
        {
            if ($this->paymentIntent->amount != $this->params['amount'])
                return true;
        }

        $this->customer->createStripeCustomerIfNotExists(true);
        $customerId = $this->customer->getStripeId();
        if (!empty($this->paymentIntent->customer) && $this->paymentIntent->customer != $customerId)
            return true;

        return false;
    }

    public function updateFrom($quote)
    {
        if (empty($quote))
            return $this;

        if (!$this->config->isEnabled())
            return $this;

        if ($this->stopUpdatesForThisSession)
            return $this;

        $this->getParamsFrom($quote);
        $this->loadFromCache($quote);
        $this->refreshCache($quote->getId());

        if (!$this->paymentIntent)
            return $this;

        if ($this->isSuccessful(false))
            return $this;

        if ($this->differentFrom($quote))
        {
            $params = $this->getFilteredParamsForUpdate();

            foreach ($params as $key => $value)
                $this->paymentIntent->{$key} = $value;

            $this->updatePaymentIntent($quote);
        }
    }

    // Performs an API update of the PI
    public function updatePaymentIntent($quote)
    {
        try
        {
            $this->paymentIntent->save();
            $this->updateCache($quote->getId());
        }
        catch (\Exception $e)
        {
            $this->log($e);
            throw $e;
        }
    }

    protected function log($e)
    {
        Logger::log("Payment Intents Error: " . $e->getMessage());
        Logger::log("Payment Intents Error: " . $e->getTraceAsString());
    }

    public function destroy($quoteId, $cancelPaymentIntent = false)
    {
        $key = 'payment_intent_' . $quoteId;
        $this->session->unsetData($key);

        if ($this->paymentIntent && $cancelPaymentIntent && $this->paymentIntent->status != $this::CANCELED)
            $this->paymentIntent->cancel();

        $this->paymentIntent = null;
        $this->params = [];

        if (isset($this->paymentIntentsCache[$key]))
            unset($this->paymentIntentsCache[$key]);
    }

    // At the final place order step, if the amount and currency has not changed, Magento will not call
    // the quote observer. But the customer may have changed the shipping address, in which case a
    // payment intent update is needed. We want to unset the amount and currency in this case because
    // the Stripe API will throw an error, because the PI has already been authorized at the checkout
    protected function getFilteredParamsForUpdate()
    {
        $params = $this->params; // clones the array
        $allowedParams = ["amount", "currency", "description", "metadata", "shipping", "level2", "level3"];

        foreach ($params as $key => $value) {
            if (!in_array($key, $allowedParams))
                unset($params[$key]);
        }

        if ($params["amount"] == $this->paymentIntent->amount)
            unset($params["amount"]);

        if ($params["currency"] == $this->paymentIntent->currency)
            unset($params["currency"]);

        if (empty($params["shipping"]))
            $params["shipping"] = null; // Unsets it through the API

        return $params;
    }

    public function differentFrom($quote)
    {
        $isAmountDifferent = ($this->paymentIntent->amount != $this->params['amount']);
        $isCurrencyDifferent = ($this->paymentIntent->currency != $this->params['currency']);
        $isPaymentMethodDifferent = !$this->samePaymentMethods($this->params['payment_method_types']);
        $isAddressDifferent = $this->isAddressDifferentFrom($quote);

        return ($isAmountDifferent || $isCurrencyDifferent || $isPaymentMethodDifferent || $isAddressDifferent);
    }

    public function isAddressDifferentFrom($quote)
    {
        $newShipping = $this->getShippingAddressFrom($quote);

        // If both are empty, they are the same
        if (empty($this->paymentIntent->shipping) && empty($newShipping))
            return false;

        // If one of them is empty, they are different
        if (empty($this->paymentIntent->shipping) && !empty($newShipping))
            return true;

        if (!empty($this->paymentIntent->shipping) && empty($newShipping))
            return true;

        $comparisonKeys1 = ["name", "phone"];
        $comparisonKeys2 = ["city", "country", "line1", "line2", "postal_code", "state"];

        foreach ($comparisonKeys1 as $key) {
            if ($this->paymentIntent->shipping->{$key} != $newShipping[$key])
                return true;
        }

        foreach ($comparisonKeys2 as $key) {
            if ($this->paymentIntent->shipping->address->{$key} != $newShipping["address"][$key])
                return true;
        }

        return false;
    }

    public function getShippingAddressFrom($quote)
    {
        $address = $quote->getShippingAddress();

        if (empty($quote) || $quote->getIsVirtual())
            return null;

        if (empty($address) || empty($address->getAddressId()))
            return null;

        if (empty($address->getFirstname()))
            $address = $this->addressFactory->create()->load($address->getAddressId());

        if (empty($address->getFirstname()))
            return null;

        $street = $address->getStreet();

        return [
            "address" => [
                "city" => $address->getCity(),
                "country" => $address->getCountryId(),
                "line1" => $street[0],
                "line2" => (!empty($street[1]) ? $street[1] : null),
                "postal_code" => $address->getPostcode(),
                "state" => $address->getRegion()
            ],
            "carrier" => null,
            "name" => $address->getFirstname() . " " . $address->getLastname(),
            "phone" => $address->getTelephone(),
            "tracking_number" => null
        ];
    }

    public function isSuccessful($fetchFromAPI = true)
    {
        if (!$this->config->isEnabled())
            return false;

        $quote = $this->getQuote();
        if (!$quote)
            return false;

        $this->loadFromCache($quote);

        if (!$this->paymentIntent)
            return false;

        // Refresh the object from the API
        try
        {
            if ($fetchFromAPI)
                $this->refreshCache($quote->getId());
        }
        catch (\Exception $e)
        {
            return false;
        }

        return $this->isSuccessfulStatus();
    }

    public function isSuccessfulStatus($paymentIntent = null)
    {
        if (empty($paymentIntent))
            $paymentIntent = $this->paymentIntent;

        return ($paymentIntent->status == PaymentIntent::CAPTURED ||
            $paymentIntent->status == PaymentIntent::AUTHORIZED);
    }

    public function refreshCache($quoteId)
    {
        if (!$this->paymentIntent)
            return;

        $this->paymentIntent = \Stripe\PaymentIntent::retrieve($this->paymentIntent->id);
        $this->updateCache($quoteId);
    }

    public function getCaptureMethod()
    {
        // Overwrite for when capturing an expired authorization
        if ($this->capture)
            return $this->capture;

        if ($this->config->isAuthorizeOnly())
            return PaymentIntent::CAPTURE_METHOD_MANUAL;

        return PaymentIntent::CAPTURE_METHOD_AUTOMATIC;
    }

    public function requiresAction()
    {
        return (
            !empty($this->paymentIntent->status) &&
            $this->paymentIntent->status == $this::REQUIRES_ACTION
        );
    }

    public function triggerAuthentication($piSecrets, $order, $payment)
    {
        if (count($piSecrets) > 0)
        {
            if ($this->helper->isAdmin())
                throw new LocalizedException(__("This card cannot be used because it requires a 3D Secure authentication by the customer."));

            // Front-end checkout case, this will trigger the 3DS modal.
            throw new \Exception("Authentication Required: " . implode(",", $piSecrets));
        }
    }

    public function redirectToMultiShippingAuthorizationPage($payment, $paymentIntentId)
    {
        $this->session->setAuthorizationRedirect("stripe/authorization/multishipping");
        $payment->setIsTransactionPending(true);
        $payment->setIsTransactionClosed(0);
        $payment->setIsFraudDetected(false);
        $payment->setAdditionalInformation('authentication_pending', true);
        $payment->setTransactionId($paymentIntentId);
        $payment->setLastTransId($paymentIntentId);

        return $this->paymentIntent;
    }

    public function confirmAndAssociateWithOrder($order, $payment)
    {
        if ($payment->getAdditionalInformation("is_recurring_subscription"))
            return null;

        $hasSubscriptions = $this->helper->hasSubscriptionsIn($order->getAllItems());

        $quote = $order->getQuote();
        if (empty($quote) || !is_numeric($quote->getGrandTotal()))
            $this->quote = $quote = $this->quoteRepository->get($order->getQuoteId());
        if (empty($quote) || !is_numeric($quote->getGrandTotal()))
            throw new \Exception("Invalid quote used for Payment Intent");

        // Save the quote so that we don't lose the reserved order ID in the case of a payment error
        $quote->save();

        // Create subscriptions if any
        $piSecrets = $this->createSubscriptionsFor($order);

        $created = $this->create($quote, $payment); // Load or create the Payment Intent

        if (!$created && $hasSubscriptions)
        {
            if (count($piSecrets) > 0 && $this->helper->isMultiShipping())
            {
                reset($piSecrets);
                $paymentIntentId = key($piSecrets); // count($piSecrets) should always be 1 here
                return $this->redirectToMultiShippingAuthorizationPage($payment, $paymentIntentId);
            }

            // This makes sure that if another quote observer is triggered, we do not update the PI
            $this->stopUpdatesForThisSession = true;

            // We may be buying a subscription which does not need a Payment Intent created manually
            if ($this->paymentIntent)
            {
                $object = clone $this->paymentIntent;
                $this->destroy($order->getQuoteId());
            }
            else
                $object = null;

            $this->triggerAuthentication($piSecrets, $order, $payment);

            // Let's save the Stripe customer ID on the order's payment in case the customer registers after placing the order
            if (!empty($this->subscriptionData['stripeCustomerId']))
                $payment->setAdditionalInformation("customer_stripe_id", $this->subscriptionData['stripeCustomerId']);

            return $object;
        }

        if (!$this->paymentIntent)
            throw new LocalizedException(__("Unable to create payment intent"));

        if (!$this->isSuccessfulStatus())
        {
            $this->order = $order;
            $save = ($this->helper->isMultiShipping() || $payment->getAdditionalInformation("save_card"));
            $this->setPaymentMethod($payment->getAdditionalInformation("token"), $save, false);
            $params = $this->config->getStripeParamsFrom($order);
            $this->paymentIntent->description = $params['description'];
            $this->paymentIntent->metadata = $params['metadata'];

            if ($this->helper->isMultiShipping())
                $this->paymentIntent->amount = $params['amount'];

            $this->updatePaymentIntent($quote);

            $confirmParams = [];

            if ($this->helper->isAdmin() && $this->config->isMOTOExemptionsEnabled())
                $confirmParams = ["payment_method_options" => ["card" => ["moto" => "true"]]];

            try
            {
                $this->paymentIntent->confirm($confirmParams);
                $this->prepareRollback();
            }
            catch (\Exception $e)
            {
                $this->prepareRollback();
                $this->helper->maskException($e);
            }

            if ($this->requiresAction())
                $piSecrets[] = $this->getClientSecret();

            if (count($piSecrets) > 0 && $this->helper->isMultiShipping())
                return $this->redirectToMultiShippingAuthorizationPage($payment, $this->paymentIntent->id);
        }

        $this->triggerAuthentication($piSecrets, $order, $payment);

        $this->processAuthenticatedOrder($order, $this->paymentIntent);

        // If this method is called, we should also clear the PI from cache because it cannot be reused
        $object = clone $this->paymentIntent;
        $this->destroy($quote->getId());

        // This makes sure that if another quote observer is triggered, we do not update the PI
        $this->stopUpdatesForThisSession = true;

        return $object;
    }

    public function prepareRollback()
    {
        if (empty($this->paymentIntent->charges->data))
            return;

        foreach ($this->paymentIntent->charges->data as $charge)
        {
            if ($charge->captured)
            {
                $this->rollback->addCharge($charge->id);
            }
            else
            {
                $this->rollback->addAuthorization($this->paymentIntent->id);
                break;
            }
        }
    }

    public function processAuthenticatedOrder($order, $paymentIntent)
    {
        $hasSubscriptions = $this->helper->hasSubscriptionsIn($order->getAllItems());
        $payment = $order->getPayment();
        $payment->setTransactionId($paymentIntent->id);
        $payment->setLastTransId($paymentIntent->id);
        $payment->setIsTransactionClosed(0);
        $payment->setIsFraudDetected(false);

        $charge = $paymentIntent->charges->data[0];

        if ($this->config->isStripeRadarEnabled() &&
            isset($charge->outcome->type) &&
            $charge->outcome->type == 'manual_review')
        {
            $payment->setAdditionalInformation("stripe_outcome_type", $charge->outcome->type);
        }

        if ($hasSubscriptions)
        {
            $items = $order->getAllItems();
            foreach ($items as $item)
            {
                // Configurable products cannot be subscriptions. Also fixes a bug where if a configurable product
                // is added to the cart, and a bundled product already exists in the cart, Magento's core productModel->load()
                // method crashes with:
                // PHP Fatal error:  Uncaught Error: Call to undefined method Magento\Bundle\Model\Product\Type::getConfigurableAttributeCollection()
                if ($item->getProductType() == "configurable") continue;

                $product = $this->helper->loadProductById($item->getProduct()->getEntityId());
                if ($product && $product->getStripeSubEnabled())
                    $item->setQtyInvoiced($item->getQtyOrdered());
                else
                    $item->setQtyInvoiced(0);
            }

            // Subscription orders cannot be manually invoiced, so we create a pending invoice until its captured from the Stripe dashboard
            $invoice = $this->helper->invoiceOrder(
                $order,
                $paymentIntent->id,
                \Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE,
                ["amount" => $paymentIntent->amount, "currency" => $paymentIntent->currency],
                false
            );

            if ($invoice)
            {
                if (!$charge->captured)
                    $invoice->setState(\Magento\Sales\Model\Order\Invoice::STATE_OPEN);

                $order->addRelatedObject($invoice);
            }
        }
        else if (!$charge->captured && $this->config->isAutomaticInvoicingEnabled())
        {
            $payment->setIsTransactionPending(true);
            $invoice = $order->prepareInvoice();
            $invoice->register();
            $order->addRelatedObject($invoice);
        }

        // Let's save the Stripe customer ID on the order's payment in case the customer registers after placing the order
        if (!empty($paymentIntent->customer))
            $payment->setAdditionalInformation("customer_stripe_id", $paymentIntent->customer);

        // Add some card details for the sales email
        $card = $paymentIntent->charges->data[0]->payment_method_details->card;
        $info = [
            'Card' => __("%1 ending **** %2", ucfirst($card->brand), $card->last4),
            'Expires' => "{$card->exp_month}/{$card->exp_year}"
        ];
        $payment->setAdditionalInformation('source_info', json_encode($info));
    }

    protected function createSubscriptionsFor($order)
    {
        if (!$this->helper->hasSubscriptionsIn($order->getAllItems()))
            return [];

        if ($this->quote)
            $quote = $this->quote; // Used when migrating subscriptions from the CLI
        else
            $quote = $this->quoteRepository->get($order->getQuoteId());

        $params = $this->getParamsFrom($quote, $order->getPayment());

        $amount = $params['amount'];
        $cents = 100;
        if ($this->helper->isZeroDecimal($params['currency']))
            $cents = 1;

        $trialEnd = $order->getPayment()->getAdditionalInformation("subscription_start");
        $this->subscriptionData = $data = $this->subscriptionsHelper->createSubscriptions($order, false, $trialEnd);
        $this->params['amount'] = round((($amount/$cents) - $data['subscriptionsTotal']) * $cents);

        $piSecrets = $data['piSecrets'];
        $createdSubscriptions = $data['createdSubscriptions'];

        if (empty($createdSubscriptions))
            return [];

        foreach ($createdSubscriptions as $key => $subscriptionId)
            $this->cache->save($subscriptionId, $key, $tags = ["unconfirmed_subscriptions"], $lifetime = 60 * 60);

        // The following is needed for the Multishipping page, in theory there should be only a single piSecret because multiple subscriptions are disallowed
        foreach ($piSecrets as $paymentIntentId => $clientSecret)
        {
            $order->getPayment()
                ->setAdditionalInformation("payment_intent_id", $paymentIntentId)
                ->setAdditionalInformation("payment_intent_client_secret", $clientSecret);
        }

        return $piSecrets;
    }

    protected function setOrderState($order, $state)
    {
        $status = $order->getConfig()->getStateDefaultStatus($state);
        $order->setState($state)->setStatus($status);
    }

    public function getDescription()
    {
        if (empty($this->paymentIntent->description))
            return null;

        return $this->paymentIntent->description;
    }

    public function setSource($sourceId)
    {
        if (!$this->config->isEnabled())
            return $this;

        $quote = $this->getQuote();

        if (!$quote)
        {
            $this->paymentIntent = null;
            return $this;
        }

        if (!$this->loadFromCache($quote))
            return $this;

        $this->paymentIntent->source = $sourceId;
        $this->updatePaymentIntent($quote);
    }

    public function setPaymentMethod($paymentMethodId, $save = false, $update = true)
    {
        $newPaymentMethod = null;

        if (!$this->config->isEnabled())
            return $this;

        $quote = $this->getQuote();

        if (!$quote)
        {
            $this->paymentIntent = null;
            return $this;
        }

        if (!$this->helper->isMultiShipping() && !$this->loadFromCache($quote))
            return $this;

        $changed = false;

        if (!$save && isset($this->paymentIntent->save_payment_method) && $this->paymentIntent->save_payment_method)
        {
            $this->paymentIntent->save_payment_method = false;
            $changed = true;
        }

        if ($save && (!isset($this->paymentIntent->save_payment_method) || !$this->paymentIntent->save_payment_method))
        {
            $this->paymentIntent->save_payment_method = true;
            $changed = true;

            // If the card is already saved, delete the old one so that the customer's saved cards are not duplicated
            // This also ensures that billing address updates are reflected in the payment
            $card = $this->customer->findCardByPaymentMethodId($paymentMethodId);
            if ($card && $paymentMethodId != $card->id && strpos($card->id, "pm_") === 0)
            {
                $paymentMethod = \Stripe\PaymentMethod::retrieve($card->id);
                if (!empty($paymentMethod->customer))
                    $paymentMethod->detach();
            }
        }

        if (!isset($this->paymentIntent->payment_method) || $this->paymentIntent->payment_method != $paymentMethodId)
        {
            $this->paymentIntent->payment_method = $paymentMethodId;
            $changed = true;
            $this->setCustomer();
        }

        if ($changed && $update)
            $this->updatePaymentIntent($quote);

        return $this;
    }

    public function setCustomer()
    {
        if ($this->helper->isGuest() && !empty($this->paymentIntent->customer))
            return;

        $this->customer->createStripeCustomerIfNotExists(true);

        $customerId = $this->customer->getStripeId();

        // Case for the REST API
        if (!$customerId && $this->order)
        {
            $this->customer->createStripeCustomer($this->order);
            $customerId = $this->customer->getStripeId();
        }

        if (!$customerId)
            throw new \Exception("Could not find a Stripe customer ID");

        if (!empty($this->paymentIntent->customer) && $this->paymentIntent->customer == $customerId)
            return;

        if (!empty($this->paymentIntent->customer) && $this->paymentIntent->customer != $customerId)
            throw new \Exception("Cannot update Stripe customer once set on the Payment Intent");

        $this->paymentIntent->customer = $this->customer->getStripeId();
    }
}
