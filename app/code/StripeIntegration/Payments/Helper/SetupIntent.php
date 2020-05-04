<?php

namespace StripeIntegration\Payments\Helper;

use StripeIntegration\Payments\Helper\Logger;
use Magento\Framework\Exception\LocalizedException;

class SetupIntent
{
    public function __construct(
        \StripeIntegration\Payments\Helper\Generic $helper,
        \StripeIntegration\Payments\Model\StripeCustomer $stripeCustomer,
        \Magento\Sales\Model\Order $order,
        \Magento\Sales\Model\Order\Invoice $invoice,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Customer\Model\Address $customerAddress,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->helper = $helper;
        $this->stripeCustomer = $stripeCustomer;
        $this->order = $order;
        $this->invoice = $invoice;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->quoteFactory = $quoteFactory;
        $this->cart = $cart;
        $this->customerAddress = $customerAddress;
        $this->transactionFactory = $transactionFactory;
        $this->cache = $cache;
        $this->urlBuilder = $urlBuilder;
    }

    public function shouldUseSetupIntents()
    {
        if ($this->helper->isAdmin())
            return false;

        if ($this->helper->hasSubscriptions())
            return true;

        return false;
    }

    public function createForCheckout()
    {
        if (!$this->shouldUseSetupIntents())
            return null;

        $setupIntent = \Stripe\SetupIntent::create();

        return $setupIntent->client_secret;
    }

    public function createForMultishippingFrom(&$paymentInfo)
    {
        if (!$this->helper->isMultiShipping())
            return;

        $this->clearAuthorizationData();

        $paymentMethodId = $paymentInfo->getAdditionalInformation('token');
        $this->stripeCustomer->createStripeCustomerIfNotExists();
        $customerId = $this->stripeCustomer->getStripeId();

        try
        {
            $setupIntentId = $paymentInfo->getAdditionalInformation('setup_intent_id');

            if (!empty($setupIntentId))
            {
                $setupIntent = \Stripe\SetupIntent::retrieve($setupIntentId);

                if ($setupIntent->payment_method != $paymentMethodId)
                    throw new \Exception("The SetupIntent payment method has changed");

                if ($setupIntent->customer != $customerId)
                    throw new \Exception("The SetupIntent customer has changed");
            }
            else
                $setupIntent = null;
        }
        catch (\Exception $e)
        {
            $setupIntent = null;
        }

        if (empty($setupIntent))
        {
            $setupIntent = \Stripe\SetupIntent::create([
                "payment_method_types" => ["card"],
                "customer" => $customerId,
                "payment_method" => $paymentMethodId,
                "description" => "Multishipping payment method setup"
            ]);
        }

        $paymentInfo->setAdditionalInformation('setup_intent_id', $setupIntent->id);

        // For existing SetupIntents
        if ($setupIntent->status == "succeeded")
            return;

        $setupIntent->confirm(["payment_method" => $paymentMethodId]); // We pass payment_method again because in multishipping, it gets lost with every confirmation

        // For new SetupIntents
        if ($setupIntent->status == "succeeded")
            return;

        if ($setupIntent->status == "requires_action")
            $this->setAuthorizationData();
        else
            throw new \Exception("Unhandled SetupIntent status {$setupIntent->status}");
    }

    public function setAuthorizationData()
    {
        $customerId = $this->customerSession->getCustomerId();
        $tags = ['stripe_payments_setup_intents'];
        $lifetime = 5 * 60; // 5 mins
        $this->cache->save($data = $this->urlBuilder->getUrl('*/*/*'), $key = $customerId . "_success_url", $tags, $lifetime);
        $this->cache->save($data = $this->urlBuilder->getUrl('*/*/billing'), $key = $customerId . "_fail_url", $tags, $lifetime);
        $this->cache->save($data = $this->urlBuilder->getUrl('stripe/authorization/multishipping'), $key = $customerId . "_authorization_url", $tags, $lifetime);
    }

    public function clearAuthorizationData()
    {
        $customerId = $this->customerSession->getCustomerId();
        $this->cache->remove($customerId . "_authorization_url");
        $this->cache->remove($customerId . "_success_url");
        $this->cache->remove($customerId . "_fail_url");
    }
}
