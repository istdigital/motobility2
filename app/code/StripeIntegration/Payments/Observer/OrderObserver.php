<?php

namespace StripeIntegration\Payments\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use StripeIntegration\Payments\Helper\Logger;

class OrderObserver extends AbstractDataAssignObserver
{
    public function __construct(
        \StripeIntegration\Payments\Model\Config $config,
        \StripeIntegration\Payments\Model\PaymentIntent $paymentIntent,
        \StripeIntegration\Payments\Helper\Generic $helper
    )
    {
        $this->config = $config;
        $this->paymentIntent = $paymentIntent;
        $this->helper = $helper;
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

        if ($method == 'stripe_payments')
        {
            switch ($eventName)
            {
                case 'sales_order_place_after':
                    $this->updateOrderState($observer);

                    // Different to M1, this is unnecessary
                    // $this->updateStripeCustomer()
                    break;
            }
        }
        // else if ($method == "stripe_payments_sepa_credit" && $eventName == "sales_order_place_after")
        // {
        //     $this->helper->sendNewOrderEmailFor($order);
        // }
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
            $comment = __("Order placed under manual review by Stripe Radar");
            $order->addStatusToHistory(false, $comment, false);
            $order->save();
        }

        if ($payment->getAdditionalInformation('authentication_pending'))
        {
            $comment = __("Customer 3D secure authentication is pending for this order.");
            $order->addStatusToHistory($status = \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT, $comment, $isCustomerNotified = false);
            $order->save();
        }
    }
}
