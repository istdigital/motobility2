<?php

namespace Cryozonic\StripePayments\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Cryozonic\StripePayments\Helper\Logger;

class OrderObserver extends AbstractDataAssignObserver
{
    public function __construct(
        \Cryozonic\StripePayments\Model\Config $config,
        \Cryozonic\StripePayments\Model\PaymentIntent $paymentIntent
    )
    {
        $this->config = $config;
        $this->paymentIntent = $paymentIntent;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $eventName = $observer->getEvent()->getName();
        $method = $order->getPayment()->getMethod();

        if ($method != 'cryozonic_stripe')
            return;

        switch ($eventName)
        {
            case 'sales_order_place_before':
                // We simply need to invalidate the local cache so that we don't try to update successful PIs
                $this->paymentIntent->isSuccessful();
                break;
            case 'sales_order_place_after':
                // Set the order status according to the configuration
                $newOrderStatus = $this->config->getNewOrderStatus();
                if ($newOrderStatus)
                    $order->addStatusToHistory($newOrderStatus, __('Changing order status as per New Order Status configuration'));

                $this->updateOrderState($observer);

                // Different to M1, this is unnecessary
                // $this->updateStripeCustomer()
                break;
        }
    }

    public function updateOrderState($observer)
    {
        $order = $observer->getEvent()->getOrder();
        $payment = $order->getPayment();

        if ($payment->getAdditionalInformation('stripe_outcome_type') == "manual_review")
        {
            $order->setHoldBeforeState($order->getState());
            $order->setHoldBeforeStatus($order->getStatus());
            $order->setState(\Magento\Sales\Model\Order::STATE_HOLDED)
                ->setStatus($order->getConfig()->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_HOLDED));
            $order->addStatusToHistory(false, "Order placed under manual review by Stripe Radar", false);
            $order->save();
        }
    }
}
