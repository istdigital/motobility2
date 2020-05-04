<?php

namespace StripeIntegration\Payments\Model\Method;

use Magento\Framework\Exception\CouldNotSaveException;

class Klarna extends \StripeIntegration\Payments\Model\Method\Api\Sources
{
    const METHOD_CODE = 'stripe_payments_klarna';
    protected $_code = self::METHOD_CODE;
    protected $type = 'klarna';
    protected $_isInitializeNeeded = false;
    protected $_canAuthorize = true;
    protected $_canCapture = true;

    public function assignData(\Magento\Framework\DataObject $data)
    {
        parent::assignData($data);

        $info = $this->getInfoInstance();
        $sourceId = $data->getAdditionalData("source_id");
        $info->setAdditionalInformation('source_id', $sourceId);

        return $this;
    }

    public function associateSourceWithOrder($payment)
    {
        $order = $payment->getOrder();
        $info = $this->getInfoInstance();
        $sourceId = $info->getAdditionalInformation("source_id");

        // Due to the nature of Klarna authorizing the payment at the front-end, we don't have an Order # in the Source
        // metadata, so we instead save it in the cache for 1 hour
        $this->cache->save($data = $order->getIncrementId(), $key = $sourceId, $tags = ["stripe_payments"], $lifetime = 12 * 60 * 60);
    }

    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $this->associateSourceWithOrder($payment);

        return parent::authorize($payment, $amount);
    }

    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $this->associateSourceWithOrder($payment);

        if ($amount > 0)
        {
            $token = $payment->getTransactionId();
            if (empty($token))
                $token = $payment->getLastTransId(); // In case where the transaction was not created during the checkout, i.e. with a Stripe Webhook redirect

            if ($token)
            {
                $token = $this->helper->cleanToken($token);
                try
                {

                    $ch = \Stripe\Charge::retrieve($token);
                    $paymentObject = $ch;
                    $amountToCapture = "amount";
                    $finalAmount = $this->helper->getMultiCurrencyAmount($payment, $amount); // $amount is the base_amount
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
                    $this->helper->dieWithError($e->getMessage(), $e);
                }
            }
        }

        return parent::capture($payment, $amount);
    }

    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if (parent::isAvailable($quote) === false) {
            return false;
        }

        if (!$this->isActive($quote ? $quote->getStoreId() : null)) {
            return false;
        }

        if (!$quote) {
            return false;
        }

        // Check if the currency is supported
        $allowedCurrencies = \StripeIntegration\Payments\Helper\Klarna::getSupportedCurrencies();

        if (!in_array($quote->getQuoteCurrencyCode(), $allowedCurrencies))
            return false;

        // Check if the country is supported
        $billingAddress = $quote->getBillingAddress();
        if (empty($billingAddress))
            return false;

        $countryId = $billingAddress->getCountryId();
        if (empty($countryId))
            return false;

        $allowedCountries = explode(",", $this->config->getConfigData('specificcountry', 'klarna'));
        if (empty($allowedCountries))
            $allowedCountries = \StripeIntegration\Payments\Helper\Klarna::getSupportedCountries();

        if (!in_array($countryId, $allowedCountries))
            return false;

        return true;
    }
}
