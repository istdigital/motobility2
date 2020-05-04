<?php

namespace StripeIntegration\Payments\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use StripeIntegration\Payments\Helper\Logger;

class PaymentMethodActiveObserver extends AbstractDataAssignObserver
{
    public function __construct(
        \StripeIntegration\Payments\Helper\Generic $helper,
        \StripeIntegration\Payments\Model\Config $config
    )
    {
        $this->helper = $helper;
        $this->config = $config;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (!$this->config->isSubscriptionsEnabled())
            return;

        $result = $observer->getEvent()->getResult();
        $methodInstance = $observer->getEvent()->getMethodInstance();
        $quote = $observer->getEvent()->getQuote();
        $code = $methodInstance->getCode();
        $isAvailable = $result->getData('is_available');

        // No need to check if its already false
        if (!$isAvailable)
            return;

        // Don't disable the Stripe payment method
        if ($code == 'stripe_payments')
            return;

        // Can't check without a quote
        if (!$quote)
            return;

        // Check if the quote contains subscriptions
        $items = $quote->getAllItems();
        if (empty($items))
            return;

        foreach ($items as $item)
        {
            $product = $this->helper->loadProductById($item->getProduct()->getEntityId());
            if ($product->getStripeSubEnabled())
            {
                // Disable for all payment methods except the module
                $result->setData('is_available', false);
                return;
            }
        }
    }
}
