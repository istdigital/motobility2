<?php

namespace Cryozonic\StripePayments\Observer;

use Magento\Framework\Event\ObserverInterface;
use Cryozonic\StripePayments\Helper\Logger;
use Cryozonic\StripePayments\Exception\WebhookException;

class WebhooksObserver implements ObserverInterface
{
    public function __construct(
        \Cryozonic\StripePayments\Helper\Webhooks $webhooksHelper,
        \Cryozonic\StripePayments\Helper\Generic $paymentsHelper,
        \Cryozonic\StripePayments\Model\Config $config,
        \Magento\Sales\Model\Order\Email\Sender\OrderCommentSender $orderCommentSender
    )
    {
        $this->webhooksHelper = $webhooksHelper;
        $this->paymentsHelper = $paymentsHelper;
        $this->config = $config;
        $this->orderCommentSender = $orderCommentSender;
    }

    protected function orderAgeLessThan($minutes, $order)
    {
        $created = strtotime($order->getCreatedAt());
        $now = time();
        return (($now - $created) < ($minutes * 60));
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $eventName = $observer->getEvent()->getName();
        $arrEvent = $observer->getData('arrEvent');
        $stdEvent = $observer->getData('stdEvent');
        $object = $observer->getData('object');

        $order = $this->webhooksHelper->loadOrderFromEvent($arrEvent);

        switch ($eventName)
        {
            // The following can trigger when:
            // 1. A merchant uses the Stripe Dashboard to manually capture a payment intent that was Authorized Only
            // 2. When a normal order is placed at the checkout, in which case we need to ignore this
            case 'cryozonic_stripe_webhook_payment_intent_succeeded':

                // This is scenario 2 which we need to ignore
                if (empty($order) || $this->orderAgeLessThan($minutes = 3, $order))
                    throw new WebhookException("Ignoring", 202);

                $paymentIntentId = $arrEvent['data']['object']['id'];
                $captureCase = \Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE;
                $params = [
                    "amount" => $arrEvent['data']['object']['amount_received'],
                    "currency" => $arrEvent['data']['object']['currency']
                ];

                $this->paymentsHelper->invoiceOrder($order, $paymentIntentId, $captureCase, $params);

                // $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)
                //     ->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING)
                //     ->save();

                break;

            case 'cryozonic_stripe_webhook_charge_refunded_card':

                $this->webhooksHelper->refund($order, $object);
                break;

            default:
                # code...
                break;
        }
    }

    public function addOrderCommentWithEmail($order, $comment)
    {
        $order->addStatusToHistory($status = false, $comment, $isCustomerNotified = true);
        $this->orderCommentSender->send($order, $notify = true, $comment);
        $order->save();
    }
}
