<?php

namespace StripeIntegration\Payments\Model;

use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Validator\ValidatorPoolInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Api\Data\CartInterface;
use StripeIntegration\Payments\Helper;
use Psr\Log\LoggerInterface;
use Magento\Framework\Validator\Exception;
use StripeIntegration\Payments\Helper\Logger;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Framework\Exception\CouldNotSaveException;

class PaymentMethod extends \Magento\Payment\Model\Method\Adapter
{
    public static $code                 = "stripe_payments";

    protected $_isInitializeNeeded      = false;
    protected $_canUseForMultishipping  = true;

    /**
     * @param ManagerInterface $eventManager
     * @param ValueHandlerPoolInterface $valueHandlerPool
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param string $code
     * @param string $formBlockType
     * @param string $infoBlockType
     * @param StripeIntegration\Payments\Model\Config $config
     * @param CommandPoolInterface $commandPool
     * @param ValidatorPoolInterface $validatorPool
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Payment\Gateway\Config\ValueHandlerPoolInterface $valueHandlerPool,
        \Magento\Payment\Gateway\Data\PaymentDataObjectFactory $paymentDataObjectFactory,
        $code,
        $formBlockType,
        $infoBlockType,
        \StripeIntegration\Payments\Model\Config $config,
        \StripeIntegration\Payments\Helper\Generic $helper,
        \StripeIntegration\Payments\Helper\Api $api,
        \StripeIntegration\Payments\Model\StripeCustomer $customer,
        \StripeIntegration\Payments\Model\PaymentIntent $paymentIntent,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Framework\App\CacheInterface $cache,
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
        $this->cache = $cache;

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
             ->setAdditionalInformation('save_card', null)
             ->setAdditionalInformation('token', null);
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
            'stripe_payments_assigndata',
            array(
                'method' => $this,
                'info' => $info,
                'data' => $data
            )
        );

        // If using a saved card
        if (!empty($data['cc_saved']) && $data['cc_saved'] != 'new_card')
        {
            $card = explode(':', $data['cc_saved']);

            $this->resetPaymentData();
            $info->setAdditionalInformation('token', $card[0]);
            $info->setAdditionalInformation('save_card', $data['cc_save']);
            $this->helper->updateBillingAddress($card[0]);

            return $this;
        }

        // Scenarios by OSC modules trying to prematurely save payment details
        if (empty($data['cc_stripejs_token']))
            return $this;

        $card = explode(':', $data['cc_stripejs_token']);
        $data['cc_stripejs_token'] = $card[0]; // To be used by Stripe Subscriptions

        // Security check: If Stripe Elements is enabled, only accept source tokens and saved cards
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

            if ($token)
            {
                $token = $this->helper->cleanToken($token);
                try
                {
                    if (strpos($token, 'pi_') === 0)
                    {
                        $pi = \Stripe\PaymentIntent::retrieve($token);
                        $ch = $pi->charges->data[0];
                        $paymentObject = $pi;
                        $amountToCapture = "amount_to_capture";
                    }
                    else
                    {
                        $ch = \Stripe\Charge::retrieve($token);
                        $paymentObject = $ch;
                        $amountToCapture = "amount";
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

                        $humanReadableAmount = strtoupper($ch->currency) . " " . round($capturedAmount / $cents, 2);
                        $this->helper->dieWithError("This invoice has already been captured in Stripe for an amount of ($humanReadableAmount). To complete the order, please re-create the Invoice using the Offline capture method, and then create an Offline Credit Memo for that Invoice (as the amount has been automatically refunded to the customer).");
                    }

                    $this->cache->save($value = "1", $key = "admin_captured_" . $paymentObject->id, ["stripe_payments"], $lifetime = 60 * 60);
                    $paymentObject->capture(array($amountToCapture => round($finalAmount * $cents)));
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
                if ($pi->status == \StripeIntegration\Payments\Model\PaymentIntent::AUTHORIZED)
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

            $params["charge"] = $charge->id;

            // This is true when an authorization has expired or when there was a refund through the Stripe account
            if (!$charge->refunded)
            {
                $this->cache->save($value = "1", $key = "admin_refunded_" . $charge->id, ["stripe_payments"], $lifetime = 60 * 60);
                \Stripe\Refund::create($params);

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

    public function canCapture()
    {
        return parent::canCapture();
    }

    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if (!$this->config->initStripe())
            return false;

        return parent::isAvailable($quote);
    }

    // The reasoning for overwriting the payment action is that subscription invoices should not be generated at order time
    // instead they should be generated upon an invoice.payment_succeeded webhook arrival
    public function getConfigPaymentAction()
    {
        $action = parent::getConfigPaymentAction();
        if ($action == \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE || $this->helper->hasSubscriptions())
            return \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE;

        return $action;
    }

    // Fixes https://github.com/magento/magento2/issues/5413 in Magento 2.1
    public function setId($code) { }
    public function getId() { return $this::$code; }
}
