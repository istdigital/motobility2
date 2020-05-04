<?php

namespace StripeIntegration\Payments\Model;

use StripeIntegration\Payments\Helper;
use StripeIntegration\Payments\Helper\Logger;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    public static $moduleName           = "Magento2";
    public static $moduleVersion        = "1.8.0";
    public static $minStripePHPVersion  = "7.0.0";
    public static $moduleUrl            = "https://stripe.com/docs/plugins/magento";
    public static $partnerId            = "pp_partner_Fs67gT2M6v3mH7";
    const STRIPE_API                    = "2020-03-02";
    public $isInitialized               = false;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Helper\Generic $helper,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Locale\Resolver $localeResolver,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
        $this->encryptor = $encryptor;
        $this->localeResolver = $localeResolver;
        $this->resourceConfig = $resourceConfig;
        $this->logger = $logger;

        $this->isInitialized = $this->initStripe();
    }

    public function canInitialize()
    {
        if (!class_exists('Stripe\Stripe'))
        {
            $this->logger->critical("The Stripe PHP library dependency has not been installed. Please follow the installation instructions at https://stripe.com/docs/plugins/magento/install#manual");
            return false;
        }

        if (version_compare(\Stripe\Stripe::VERSION, \StripeIntegration\Payments\Model\Config::$minStripePHPVersion) < 0)
        {
            $version = \StripeIntegration\Payments\Model\Config::$moduleVersion;
            $this->logger->critical("Stripe Payments v$version now depends on Stripe PHP library v7. Please upgrade your installed Stripe PHP library with the command: composer require stripe/stripe-php:^7");
            return false;
        }

        return true;
    }

    public function initStripe($mode = null)
    {
        if ($this->isInitialized)
            return true;

        if (!$this->canInitialize())
            return false;

        if ($this->getSecretKey($mode) && $this->getPublishableKey($mode))
        {
            \Stripe\Stripe::setApiKey($this->getSecretKey($mode));
            \Stripe\Stripe::setAppInfo($this::$moduleName, $this::$moduleVersion, $this::$moduleUrl, $this::$partnerId);
            \Stripe\Stripe::setApiVersion(\StripeIntegration\Payments\Model\Config::STRIPE_API);
            return true;
        }

        return false;
    }

    public function reInitStripe($mode = null)
    {
        $this->isInitialized = false;
        return $this->isInitialized = $this->initStripe();
    }

    public static function module()
    {
        return self::$moduleName . " v" . self::$moduleVersion;
    }

    public function getConfigData($field, $method = null, $storeId = null)
    {
        if (empty($storeId))
            $storeId = $this->helper->getStoreId();

        $section = "";
        if ($method)
            $section = "_$method";

        $data = $this->scopeConfig->getValue("payment/stripe_payments$section/$field", ScopeInterface::SCOPE_STORE, $storeId);

        return $data;
    }

    public function setConfigData($field, $value, $method = null, $scope = null, $storeId = null)
    {
        if (empty($storeId))
            $storeId = $this->helper->getStoreId();

        if (empty($scope))
            $scope = ScopeInterface::SCOPE_STORE;

        $section = "";
        if ($method)
            $section = "_$method";

        $data = $this->resourceConfig->saveConfig("payment/stripe_payments$section/$field", $value, $scope, $storeId);

        return $data;
    }

    public function getPRAPIDescription()
    {
        $seller = $this->getConfigData('seller_name', 'express');
        if (empty($seller))
            return __("for order");
        else
            return $seller;
    }

    public function isSubscriptionsEnabled($storeId = null)
    {
        $enabled = ((bool)$this->getConfigData('active', 'subscriptions', $storeId)) && $this->initStripe();
        return $enabled;
    }

    public function isEnabled()
    {
        $enabled = ((bool)$this->getConfigData('active')) && $this->initStripe();
        return $enabled;
    }

    public function getStripeMode()
    {
        return $this->getConfigData('stripe_mode', 'basic');
    }

    public function getSecretKey($mode = null)
    {
        if (empty($mode))
            $mode = $this->getStripeMode();

        $key = $this->getConfigData("stripe_{$mode}_sk", "basic");

        return $this->decrypt($key);
    }

    public function decrypt($key)
    {
         if (!preg_match('/^[A-Za-z0-9_]+$/', $key))
            $key = $this->encryptor->decrypt($key);

        return trim($key);
    }

    public function getPublishableKey($mode = null)
    {
        if (empty($mode))
            $mode = $this->getStripeMode();

        return trim($this->getConfigData("stripe_{$mode}_pk", "basic"));
    }

    public function getWebhooksSigningSecret()
    {
        $mode = $this->getStripeMode();
        $key = $this->getConfigData("stripe_{$mode}_wss", "basic");

        // The following is due to a magento bug causing the key to need to be saved more than once to be decrypted correctly
        if (!preg_match('/^[A-Za-z0-9_]+$/',$key))
            $key = $this->encryptor->decrypt($key);

        return trim($key);
    }

    public function getWebhooksSigningSecretFor($store, $mode)
    {
        $key = $this->scopeConfig->getValue("payment/stripe_payments_basic/stripe_{$mode}_pk", \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);

        // The following is due to a magento bug causing the key to need to be saved more than once to be decrypted correctly
        if (!preg_match('/^[A-Za-z0-9_]+$/',$key))
            $key = $this->encryptor->decrypt($key);

        return trim($key);
    }

    public function isAutomaticInvoicingEnabled()
    {
        return (bool)$this->getConfigData("automatic_invoicing");
    }

    public function getSecurityMethod()
    {
        // Older security methods have been depreciated
        return 2;
    }

    // If the module is unconfigured, payment_action will be null, defaulting to authorize & capture, so this would still return the correct value
    public function isAuthorizeOnly()
    {
        return ($this->getConfigData('payment_action') == \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE);
    }

    public function isStripeRadarEnabled()
    {
        return ($this->getConfigData('radar_risk_level') > 0);
    }

    public function isApplePayEnabled()
    {
        return $this->getConfigData('apple_pay_checkout', 'express')
            && !$this->helper->isAdmin();
    }

    public function isPaymentRequestButtonEnabled()
    {
        return $this->isApplePayEnabled();
    }

    public function useStoreCurrency()
    {
        return (bool)$this->getConfigData('use_store_currency');
    }

    public function getSaveCards()
    {
        return $this->getConfigData('ccsave');
    }

    public function getStatementDescriptor()
    {
        return $this->getConfigData('statement_descriptor');
    }

    public function retryWithSavedCard()
    {
        return $this->getConfigData('expired_authorizations') == 1;
    }

    public function setIsStripeAPIKeyError($isError)
    {
        $this->isStripeAPIKeyError = $isError;
    }

    public function alwaysSaveCards()
    {
        return ($this->getSaveCards() == 2 || $this->helper->hasSubscriptions() || $this->helper->isMultiShipping());
    }

    public function isMOTOExemptionsEnabled()
    {
        return (bool)$this->getConfigData('moto_exemptions');
    }

    public function getIsStripeAPIKeyError()
    {
        if (isset($this->isStripeAPIKeyError))
            return $this->isStripeAPIKeyError;

        return false;
    }

    public function getApplePayLocation()
    {
        $location = $this->getConfigData('apple_pay_location', 'express');

        if (!$location)
            return 1;
        else
            return (int)$location;
    }

    public function getStripeJsLocale()
    {
        $supportedValues = ["ar", "da", "de", "en", "es", "fi", "fr", "he", "it", "ja", "lt", "ms", "nl", "no", "pl", "ru", "sv", "zh"];

        $locale = $this->localeResolver->getLocale();
        if (empty($locale))
            return "auto";

        $lang = strstr($locale, '_', true);
        if (in_array($lang, $supportedValues))
            return $lang;

        return "auto";
    }

    public function getAmountCurrencyFromQuote($quote, $useCents = true)
    {
        $params = array();
        $items = $quote->getAllItems();

        if ($this->useStoreCurrency())
        {
            $amount = $quote->getGrandTotal();
            $currency = $quote->getQuoteCurrencyCode();
        }
        else
        {
            $amount = $quote->getBaseGrandTotal();;
            $currency = $quote->getBaseCurrencyCode();
        }

        if ($useCents)
        {
            $cents = 100;
            if ($this->helper->isZeroDecimal($currency))
                $cents = 1;

            $fields["amount"] = round($amount * $cents);
        }
        else
        {
            // Used for Apple Pay only
            $fields["amount"] = number_format($amount, 2, '.', '');
        }

        $fields["currency"] = $currency;

        return $fields;
    }

    // Overwrite this based on business needs
    public function getMetadata($order)
    {
        return [
            "Module" => Config::module(),
            "Order #" => $order->getIncrementId()
        ];
    }

    public function getStripeParamsFrom($order)
    {
        if ($this->useStoreCurrency())
        {
            $amount = $order->getGrandTotal();
            $currency = $order->getOrderCurrencyCode();
        }
        else
        {
            $amount = $order->getBaseGrandTotal();
            $currency = $order->getBaseCurrencyCode();
        }

        $cents = 100;
        if ($this->helper->isZeroDecimal($currency))
            $cents = 1;

        $metadata = $this->getMetadata($order);

        if ($order->getCustomerIsGuest())
        {
            $customer = $this->helper->getGuestCustomer($order);
            $customerName = $customer->getFirstname() . ' ' . $customer->getLastname();
            $metadata["Guest"] = "Yes";
        }
        else
            $customerName = $order->getCustomerName();

        if ($this->helper->isMultiShipping())
            $description = "Multi-shipping Order #" . $order->getRealOrderId().' by ' . $customerName;
        else
            $description = "Order #" . $order->getRealOrderId().' by ' . $customerName;

        $params = array(
          "amount" => round($amount * $cents),
          "currency" => $currency,
          "description" => $description,
          "metadata" => $metadata
        );

        $customerEmail = $this->helper->getCustomerEmail();
        if ($customerEmail)
            $params["receipt_email"] = $customerEmail;

        return $params;
    }

}
