<?php

namespace Cryozonic\StripePayments\Helper;

use Cryozonic\StripePayments\Helper\Logger;
use Cryozonic\StripePayments\Exception\WebhookException;

class Webhooks
{
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Response\Http $response,
        \Cryozonic\StripePayments\Logger\WebhooksLogger $webhooksLogger,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Cryozonic\StripePayments\Helper\Api $api,
        \Cryozonic\StripePayments\Helper\Generic $helper,
        \Cryozonic\StripePayments\Model\StripeCustomer $stripeCustomer,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Cryozonic\StripePayments\Model\Config $config,
        \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory,
        \Magento\Sales\Model\Service\CreditmemoService $creditmemoService,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Sales\Model\Order\Invoice $invoiceModel
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->webhooksLogger = $webhooksLogger;
        $this->logger = $logger;
        $this->cache = $cache;
        $this->api = $api;
        $this->helper = $helper;
        $this->stripeCustomer = $stripeCustomer;
        $this->eventManager = $eventManager;
        $this->orderSender = $orderSender;
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->creditmemoFactory = $creditmemoFactory;
        $this->creditmemoService = $creditmemoService;
        $this->transactionFactory = $transactionFactory;
        $this->invoiceModel = $invoiceModel;
    }

    public function dispatchEvent()
    {
        try
        {
            // Retrieve the request's body and parse it as JSON
            $body = $this->request->getContent();
            $event = json_decode($body, true);
            $stdEvent = json_decode($body);

            if (empty($event['type']))
                throw new WebhookException(__("Unknown event type"));

            $eventType = "cryozonic_stripe_webhook_" . str_replace(".", "_", $event['type']);

            if (isset($event['data']['object']['type'])) // Bancontact, Giropay, iDEAL
                $eventType .= "_" . $event['data']['object']['type'];
            else if (isset($event['data']['object']['source']['type'])) // SOFORT and SEPA
                $eventType .= "_" . $event['data']['object']['source']['type'];
            else if (isset($event['data']['object']['source']['object'])) // ACH bank accounts
                $eventType .= "_" . $event['data']['object']['source']['object'];

            // Magento 2 event names do not allow numbers
            $eventType = str_replace("p24", "przelewy", $eventType);

            $this->log("Received $eventType");

            $this->cache($event);

            $this->eventManager->dispatch($eventType, array(
                    'arrEvent' => $event,
                    'stdEvent' => $stdEvent,
                    'object' => $event['data']['object']
                ));

            $this->log("200 OK");
        }
        catch (WebhookException $e)
        {
            $this->error($e->getMessage(), $e->statusCode);
        }
        catch (\Exception $e)
        {
            $this->log($e->getMessage());
            $this->log($e->getTraceAsString());
            $this->error($e->getMessage());
        }
    }

    public function error($msg, $status = null)
    {
        if ($status && $status > 0)
            $responseStatus = $status;
        else
            $responseStatus = 202;

        $this->response
            ->setStatusCode($responseStatus)
            ->setContent($msg);

        $this->log("$responseStatus $msg");
    }

    public function log($msg)
    {
        $this->webhooksLogger->addInfo($msg);
    }

    public function cache($event)
    {
        // Don't cache or check requests in development
        if (!empty($this->request->getQuery()->dev))
            return;

        if (empty($event['id']))
            throw new WebhookException("No event ID specified");

        if ($this->cache->load($event['id']))
            throw new WebhookException("Event with ID {$event['id']} has already been processed.", 202);

        $this->cache->save("processed", $event['id'], array('cryozonic_stripe_webhooks_events_processed'), 24 * 60 * 60);
    }

    public function loadOrderFromEvent($event)
    {
        $object = $event['data']['object'];

        if (isset($object['payment_intent']))
            $pi = \Stripe\PaymentIntent::retrieve($object['payment_intent']);

        if (isset($object['metadata']['Order #'])) // source.* events
            $orderId = $object['metadata']['Order #'];
        else if (isset($object['source']['metadata']['Order #'])) // charge.* events
            $orderId = $object['source']['metadata']['Order #'];
        else if (isset($object['lines']['data'][0]['metadata']['Order #'])) // invoice.payment_succeeded
            $orderId = isset($object['lines']['data'][0]['metadata']['Order #']);
        else if (isset($object['charges']['data'][0]['metadata']['Order #'])) // payment_intent.succeeded
            $orderId = isset($object['charges']['data'][0]['metadata']['Order #']);
        else if (isset($pi['metadata']['Order #']))
            $orderId = $pi['metadata']['Order #'];
        else
            throw new WebhookException("Received {$event['type']} webhook but there was no Order # in the source's metadata", 202);

        $order = $this->helper->loadOrderByIncrementId($orderId);
        if (empty($order) || empty($order->getId()))
            throw new WebhookException("Received {$event['type']} webhook with Order #$orderId but could not find the order in Magento", 202);

        $paymentMethodCode = $order->getPayment()->getMethod();
        if (strpos($paymentMethodCode, "cryozonic") !== 0)
            throw new WebhookException("Order #$orderId was not placed using Stripe", 202);

        // For multi-stripe account configurations, load the correct Stripe API key from the correct store view
        $this->storeManager->setCurrentStore($order->getStoreId());
        $this->config->initStripe();

        return $order;
    }

    // Called after a source.chargable event
    public function charge($order, $object, $addTransaction = true)
    {
        $orderId = $order->getIncrementId();

        $payment = $order->getPayment();
        if (!$payment)
            throw new WebhookException("Could not load payment method for order #$orderId");

        $orderSourceId = $payment->getAdditionalInformation('source_id');
        $webhookSourceId = $object['id'];
        if ($orderSourceId != $webhookSourceId)
            throw new WebhookException("Received source.chargeable webhook for order #$orderId but the source ID on the webhook $webhookSourceId was different than the one on the order $orderSourceId");

        // Authorize Only is not supported for this payment type
        // if (Mage::getStoreConfig('payment/cryozonic_stripe/payment_action') == Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE)
        //     $capture = true;
        // else
        //     $capture = false;

        $stripeParams = $this->api->getStripeParamsFrom($order);

        // Reusable sources may not have an amount set
        if (empty($object['amount']))
        {
            $amount = $stripeParams['amount'];
        }
        else
        {
            $amount = $object['amount'];
        }

        $params = array(
            "amount" => $amount,
            "currency" => $object['currency'],
            "source" => $webhookSourceId,
            "description" => $stripeParams['description'],
            "metadata" => $stripeParams['metadata']
            // "capture" => $capture // Not supported by Stripe
        );

        // For reusable sources, we will always need a customer ID
        $customerStripeId = $payment->getAdditionalInformation('customer_stripe_id');
        if (!empty($customerStripeId))
            $params["customer"] = $customerStripeId;

        try
        {
            $charge = \Stripe\Charge::create($params);

            // Possibly log additional info about the payment
            $info = $object[$object['type']];
            unset($info['mandate_url']);
            unset($info['fingerprint']);
            unset($info['client_token']);
            $payment->setTransactionId($charge->id);
            $payment->setLastTransId($charge->id);
            $payment->setIsTransactionClosed(0);

            $payment->setAdditionalInformation('source_info', json_encode($info));
            $payment->save();

            if ($addTransaction)
            {
                if ($charge->status == 'pending')
                    $transactionType = \Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH;
                else
                    $transactionType = \Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE;
                //Transaction::TYPE_PAYMENT

                $transaction = $payment->addTransaction($transactionType, null, false);
                $transaction->save();
            }

            if ($charge->status == 'succeeded')
            {
                $invoice = $this->helper->invoiceOrder($order, $charge->id);
                $this->sendNewOrderEmailFor($order);
            }
            // SEPA, SOFORT and other asynchronous methods will be pending
            else if ($charge->status == 'pending')
            {
                $invoice = $this->helper->invoicePendingOrder($order, \Magento\Sales\Model\Order\Invoice::NOT_CAPTURE, $charge->id);
                $this->sendNewOrderEmailFor($order);
            }
            else
            {
                // In theory we should never have failed charges because they would throw an exception
                $comment = "Authorization failed. Transaction ID: {$charge->id}. Charge status: {$charge->status}";
                $order->addStatusHistoryComment($comment);
                $order->save();
            }

        }
        catch (\Stripe\Error\Card $e)
        {
            $comment = "Order could not be charged because of a card error: " . $e->getMessage();
            $order->addStatusHistoryComment($comment);
            $order->save();
            $this->log($e->getMessage());
            throw new WebhookException($e->getMessage(), 202);
        }
        catch (\Stripe\Error $e)
        {
            $comment = "Order could not be charged because of a Stripe error.";
            $order->addStatusHistoryComment($comment);
            $order->save();
            $this->log($e->getMessage());
            throw new WebhookException($e->getMessage(), 202);
        }
        catch (\Exception $e)
        {
            $comment = "Order could not be charged because of server side error.";
            $order->addStatusHistoryComment($comment);
            $order->save();
            $this->log($e->getMessage());
            throw new WebhookException($e->getMessage(), 202);
        }
    }

    public function getCurrentRefundFrom($webhookData)
    {
        $lastRefundDate = 0;
        $currentRefund = null;

        foreach ($webhookData['refunds']['data'] as $refund)
        {
            // There might be multiple refunds, and we are looking for the most recent one
            if ($refund['created'] > $lastRefundDate)
            {
                $lastRefundDate = $refund['created'];
                $currentRefund = $refund;
            }
        }

        return $currentRefund;
    }

    public function refund($order, $object)
    {
        $dbTransaction = $this->transactionFactory->create();

        if (!$order->canCreditmemo())
            throw new WebhookException("Order #{$order->getIncrementId()} cannot be (or has already been) refunded.");

        // Check if the order has an invoice with the charge ID we are refunding
        $chargeId = $object['id'];
        $chargeAmount = $object['amount'];
        $currentRefund = $this->getCurrentRefundFrom($object);
        $refundId = $currentRefund['id'];
        $refundAmount = $currentRefund['amount'];
        $currency = $object['currency'];
        $invoice = null;
        $baseToOrderRate = $order->getBaseToOrderRate();
        $payment = $order->getPayment();
        $lastRefundId = $payment->getAdditionalInformation('last_refund_id');
        if (isset($object["payment_intent"]))
            $pi = $object["payment_intent"];
        else
            $pi = "not_exists";

        if (!empty($lastRefundId) && $lastRefundId == $refundId)
        {
            // This is the scenario where we issue a refund from the admin area, and a webhook comes back about the issued refund.
            // Magento would have already created a credit memo, so we don't want to duplicate that. We just ignore the webhook.
            return;
        }

        // Calculate the real refund amount
        if (!$this->helper->isZeroDecimal($currency))
        {
            $refundAmount /= 100;
        }

        foreach($order->getInvoiceCollection() as $item)
        {
            $transactionId = $this->helper->cleanToken($item->getTransactionId());
            if ($transactionId == $chargeId || $transactionId == $pi)
                $invoice = $item;
        }

        if (empty($invoice))
            throw new WebhookException("Could not find an invoice with transaction ID $chargeId.");

        if (!$invoice->canRefund())
            throw new WebhookException("Invoice #{$invoice->getIncrementId()} cannot be (or has already been) refunded.");

        $baseTotalNotRefunded = $invoice->getBaseGrandTotal() - $invoice->getBaseTotalRefunded();
        $baseOrderCurrency = strtolower($invoice->getBaseCurrencyCode());
        $orderCurrency = strtolower($invoice->getOrderCurrencyCode());

        if ($baseOrderCurrency != $currency)
            $refundAmount /= $order->getBaseToOrderRate();

        if ($baseTotalNotRefunded < $refundAmount)
            throw new WebhookException("Error: Trying to refund an amount that is larger than the invoice amount");

        $creditmemo = $this->creditmemoFactory->createByOrder($order);
        $creditmemo->setInvoice($invoice);

        if ($baseTotalNotRefunded > $refundAmount)
        {
            $baseDiff = $baseTotalNotRefunded - $refundAmount;
            $creditmemo->setAdjustmentNegative($baseDiff);
        }

        $creditmemo->setBaseSubtotal($baseTotalNotRefunded);
        $creditmemo->setSubtotal($baseTotalNotRefunded * $baseToOrderRate);
        $creditmemo->setBaseGrandTotal($refundAmount);
        $creditmemo->setGrandTotal($refundAmount * $baseToOrderRate);

        $this->creditmemoService->refund($creditmemo, true);

        $order->addStatusToHistory($status = false, "Order refunded through Stripe");

        $payment->setAdditionalInformation('last_refund_id', $refundId);

        $dbTransaction->addObject($invoice)
            ->addObject($order)
            ->addObject($creditmemo)
            ->addObject($payment)
            ->save();
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
}
