<?php

namespace Cryozonic\StripePayments\Model;

use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Config\ValueHandlerPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Gateway\Validator\ValidatorPoolInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Api\Data\CartInterface;
use Cryozonic\StripePayments\Helper;
use Psr\Log\LoggerInterface;
use Magento\Framework\Validator\Exception;
use Cryozonic\StripePayments\Helper\Logger;
use Magento\Payment\Observer\AbstractDataAssignObserver;

class PaymentMethod extends \Magento\Payment\Model\Method\Adapter
{
    public static $code                 = "cryozonic_stripe";

    protected $_isInitializeNeeded      = false;
    protected $_canUseForMultishipping  = true;

    /**
     * @param ManagerInterface $eventManager
     * @param ValueHandlerPoolInterface $valueHandlerPool
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param string $code
     * @param string $formBlockType
     * @param string $infoBlockType
     * @param Cryozonic\StripePayments\Model\Config $config
     * @param CommandPoolInterface $commandPool
     * @param ValidatorPoolInterface $validatorPool
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        ValueHandlerPoolInterface $valueHandlerPool,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        $code,
        $formBlockType,
        $infoBlockType,
        \Cryozonic\StripePayments\Model\Config $config,
        \Cryozonic\StripePayments\Helper\Generic $helper,
        \Cryozonic\StripePayments\Helper\Api $api,
        \Cryozonic\StripePayments\Model\StripeCustomer $customer,
        \Cryozonic\StripePayments\Model\PaymentIntent $paymentIntent,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        LoggerInterface $logger,
        CommandPoolInterface $commandPool = null,
        ValidatorPoolInterface $validatorPool = null
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->api = $api;
        $this->customer = $customer;
        $this->paymentIntent = $paymentIntent;
        $this->checkoutHelper = $checkoutHelper;
        $this->saveCards = $config->getSaveCards();
        $this->eventManager = $eventManager;

        parent::__construct(
            $eventManager,
            $valueHandlerPool,
            $paymentDataObjectFactory,
            $code,
            $formBlockType,
            $infoBlockType,
            $commandPool,
            $validatorPool
        );
    }

    protected function resetPaymentData()
    {
        $info = $this->getInfoInstance();

        // Reset a previously initialized 3D Secure session
        $info->setAdditionalInformation('stripejs_token', null)
             ->setAdditionalInformation('switch_subscription', null);
    }

    public function assignData(\Magento\Framework\DataObject $data)
    {
        if ($this->helper->isMultiShipping())
            $data['cc_save'] = 1;

        parent::assignData($data);

        if ($this->config->getIsStripeAPIKeyError())
            $this->helper->dieWithError("Invalid API key provided");

        // From Magento 2.0.7 onwards, the data is passed in a different property
        $additionalData = $data->getAdditionalData();
        if (is_array($additionalData))
            $data->setData(array_merge($data->getData(), $additionalData));

        $info = $this->getInfoInstance();
        $session = $this->checkoutHelper->getCheckout();

        $this->eventManager->dispatch(
            'cryozonic_stripepayments_assigndata',
            array(
                'method' => $this,
                'info' => $info,
                'data' => $data
            )
        );

        // If using a saved card
        if (!empty($data['cc_saved']) && $data['cc_saved'] != 'new_card' && empty($data['cc_stripejs_token']))
        {
            $card = explode(':', $data['cc_saved']);

            $this->resetPaymentData();
            $info->setAdditionalInformation('token', $card[0]);
            $info->setAdditionalInformation('save_card', $data['cc_save']);

            return $this;
        }

        // Scenarios by OSC modules trying to prematurely save payment details
        if (empty($data['cc_stripejs_token']))
            return $this;

        $card = explode(':', $data['cc_stripejs_token']);
        $data['cc_stripejs_token'] = $card[0]; // To be used by Stripe Subscriptions

        if (!$this->helper->isValidToken($card[0]))
            $this->helper->dieWithError("Sorry, we could not perform a card security check. Please contact us to complete your purchase.");

        $this->resetPaymentData();
        $token = $card[0];
        $info->setAdditionalInformation('stripejs_token', $token);
        $info->setAdditionalInformation('save_card', $data['cc_save']);
        $info->setAdditionalInformation('token', $token);

        return $this;
    }

    public function authorize(InfoInterface $payment, $amount)
    {
        if ($amount > 0)
        {
            $this->paymentIntent->confirmAndAssociateWithOrder($payment->getOrder(), $payment);
        }

        return $this;
    }

    public function capture(InfoInterface $payment, $amount)
    {
        if ($amount > 0)
        {
            // We get in here when the store is configured in Authorize Only mode and we are capturing a payment from the admin
            $token = $payment->getTransactionId();
            if (empty($token))
                $token = $payment->getLastTransId(); // In case where the transaction was not created during the checkout, i.e. with a Stripe Webhook redirect

            if ($this->helper->isAdmin() && $token)
            {
                $token = $this->helper->cleanToken($token);
                try
                {
                    if (strpos($token, 'pi_') === 0)
                    {
                        $pi = \Stripe\PaymentIntent::retrieve($token);
                        $ch = $pi->charges->data[0];
                        $paymentObject = $pi;
                    }
                    else
                    {
                        $ch = \Stripe\Charge::retrieve($token);
                        $paymentObject = $ch;
                    }

                    if ($this->config->useStoreCurrency())
                        $finalAmount = $this->helper->getMultiCurrencyAmount($payment, $amount);
                    else
                        $finalAmount = $amount;

                    $currency = $payment->getOrder()->getOrderCurrencyCode();
                    $cents = 100;
                    if ($this->helper->isZeroDecimal($currency))
                        $cents = 1;

                    if ($ch->captured)
                    {
                        // In theory this condition should never evaluate, but is added for safety
                        if ($ch->currency != strtolower($currency))
                            $this->helper->dieWithError("This invoice has already been captured in Stripe using a different currency ({$ch->currency}).");

                        $capturedAmount = $ch->amount - $ch->amount_refunded;

                        if ($capturedAmount != round($finalAmount * $cents))
                        {
                            $humanReadableAmount = strtoupper($ch->currency) . " " . round($capturedAmount / $cents, 2);
                            $this->helper->dieWithError("This invoice has already been captured in Stripe for a different amount ($humanReadableAmount). Please cancel and create a new offline invoice for the correct amount.");
                        }

                        // We return instead of trying to capture the payment to simulate an Offline capture
                        return $this;
                    }

                    $paymentObject->capture(array('amount' => round($finalAmount * $cents)));
                }
                catch (\Exception $e)
                {
                    $this->logger->critical($e->getMessage());

                    if ($this->helper->isAuthorizationExpired($e->getMessage()) && $this->config->retryWithSavedCard())
                        $this->api->createCharge($payment, $amount, true, true);
                    else
                        $this->helper->dieWithError($e->getMessage(), $e);
                }
            }
            else
            {
                $this->paymentIntent->confirmAndAssociateWithOrder($payment->getOrder(), $payment);
            }
        }

        return $this;
    }

    public function cancel(InfoInterface $payment, $amount = null)
    {
        if ($this->config->useStoreCurrency())
        {
            // Captured
            $creditmemo = $payment->getCreditmemo();
            if (!empty($creditmemo))
            {
                $rate = $creditmemo->getBaseToOrderRate();
                if (!empty($rate) && is_numeric($rate) && $rate > 0)
                    $amount *= $rate;
            }
            // Authorized
            $amount = (empty($amount)) ? $payment->getOrder()->getTotalDue() : $amount;

            $currency = $payment->getOrder()->getOrderCurrencyCode();
        }
        else
        {
            // Authorized
            $amount = (empty($amount)) ? $payment->getOrder()->getBaseTotalDue() : $amount;

            $currency = $payment->getOrder()->getBaseCurrencyCode();
        }

        $transactionId = $payment->getParentTransactionId();

        // With asynchronous payment methods, the parent transaction may be empty
        if (empty($transactionId))
            $transactionId = $payment->getLastTransId();

        // Case where an invoice is in Pending status, with no transaction ID, receiving a source.failed event which cancels the invoice.
        if (empty($transactionId))
            return $this;

        $transactionId = preg_replace('/-.*$/', '', $transactionId);

        try {
            $cents = 100;
            if ($this->helper->isZeroDecimal($currency))
                $cents = 1;

            $params = array();
            if ($amount > 0)
                $params["amount"] = round($amount * $cents);

            if (strpos($transactionId, 'pi_') === 0)
            {
                $pi = \Stripe\PaymentIntent::retrieve($transactionId);
                if ($pi->status == \Cryozonic\StripePayments\Model\PaymentIntent::AUTHORIZED)
                {
                    $pi->cancel();
                    return $this;
                }
                else
                    $charge = $pi->charges->data[0];
            }
            else
            {
                $charge = $this->api->retrieveCharge($transactionId);
            }

            // This is true when an authorization has expired or when there was a refund through the Stripe account
            if (!$charge->refunded)
            {
                $charge->refund($params);
                // \Stripe\Refund::create($params);

                $refundId = $this->helper->getRefundIdFrom($charge);
                $payment->setAdditionalInformation('last_refund_id', $refundId);
            }
            else
            {
                $msg = __('This order has already been refunded in Stripe. To refund from Magento, please refund it offline.');
                throw new LocalizedException($msg);
            }
        }
        catch (\Exception $e)
        {
            $this->logger->addError('Could not refund payment: '.$e->getMessage());
            throw new \Exception(__($e->getMessage()));
        }

        return $this;
    }

    public function cancelInvoice($invoice)
    {
        return $this;
    }

    public function refund(InfoInterface $payment, $amount)
    {
        $this->cancel($payment, $amount);

        return $this;
    }

    public function void(InfoInterface $payment)
    {
        $this->cancel($payment);

        return $this;
    }

    public function acceptPayment(InfoInterface $payment)
    {
        return parent::acceptPayment($payment);
    }

    public function denyPayment(InfoInterface $payment)
    {
        return parent::denyPayment($payment);
    }

    // Fixes https://github.com/magento/magento2/issues/5413 in Magento 2.1
    public function setId($code) { }
    public function getId() { return $this::$code; }
}
