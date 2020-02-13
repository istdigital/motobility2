<?php

namespace Cryozonic\StripeExpress\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Cryozonic\StripePayments\Helper\Logger;

class Button extends Template
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Cryozonic\StripePayments\Model\Config
     */
    protected $config;

    /**
     * @var \Cryozonic\StripeExpress\Helper\Helper
     */
    protected $helper;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutHelper;

    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $taxHelper;

    /**
     * @var \Magento\Framework\Locale\Resolver
     */
    protected $localeResolver;

    /**
     * Button constructor.
     *
     * @param Template\Context                       $context
     * @param Registry                               $registry
     * @param PriceCurrencyInterface                 $priceCurrency
     * @param \Cryozonic\StripePayments\Model\Config $config
     * @param \Cryozonic\StripeExpress\Helper\Helper $helper
     * @param \Magento\Checkout\Helper\Data          $checkoutHelper
     * @param \Magento\Tax\Helper\Data               $taxHelper
     * @param \Magento\Framework\Locale\Resolver     $localeResolver
     * @param array                                  $data
     */
    public function __construct(
        Template\Context $context,
        Registry $registry,
        PriceCurrencyInterface $priceCurrency,
        \Cryozonic\StripePayments\Model\Config $config,
        \Cryozonic\StripePayments\Helper\Generic $paymentsHelper,
        \Cryozonic\StripeExpress\Helper\Helper $helper,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Framework\Locale\Resolver $localeResolver,
        array $data = []
    ) {

        parent::__construct($context, $data);

        $this->registry = $registry;
        $this->priceCurrency = $priceCurrency;
        $this->config = $config;
        $this->helper = $helper;
        $this->urlBuilder = $context->getUrlBuilder();
        $this->checkoutHelper = $checkoutHelper;
        $this->taxHelper = $taxHelper;
        $this->localeResolver = $localeResolver;
        $this->paymentsHelper = $paymentsHelper;
    }

    /**
     * Check Is Block enabled
     * @return bool
     */
    public function isEnabled($location)
    {
        return $this->helper->isEnabled($location);
    }

    /**
     * Get Publishable Key
     * @return string
     */
    public function getPublishableKey()
    {
        return $this->config->getPublishableKey();
    }

    /**
     * Get Button Config
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getButtonConfig()
    {
        return [
            'type' => $this->helper->getStoreConfig('payment/cryozonic_stripeexpress/button_type'),
            'theme' => $this->helper->getStoreConfig('payment/cryozonic_stripeexpress/button_theme'),
            'height' => $this->helper->getStoreConfig('payment/cryozonic_stripeexpress/button_height')
        ];
    }

    /**
     * Get Payment Request Params
     * @return array
     */
    public function getApplePayParams()
    {
        if ($this->paymentsHelper->hasSubscriptions())
            return null;

        return array_merge(
            [
                'country' => $this->getCountry(),
                'requestPayerName' => true,
                'requestPayerEmail' => true,
                'requestPayerPhone' => true,
                'requestShipping' => !$this->getQuote()->isVirtual(),
            ],
            $this->helper->getCartItems($this->getQuote())
        );
    }

    /**
     * Get Payment Request Params for Single Product
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductApplePayParams()
    {
        if ($this->paymentsHelper->hasSubscriptions())
            return null;

        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->registry->registry('product');

        if (!$product || $product->getCryozonicSubEnabled()) {
            return [];
        }

        $quote = $this->getQuote();

        $currency = $quote->getQuoteCurrencyCode();
        if (empty($currency)) {
            $currency = $quote->getStore()->getCurrentCurrency()->getCode();
        }

        // Get Current Items in Cart
        $params = $this->helper->getCartItems($quote);
        $amount = $params['total']['amount'];
        $items = $params['displayItems'];

        $shouldInclTax = $this->helper->shouldCartPriceInclTax($quote->getStore());
        if ($this->helper->getStoreConfig('payment/cryozonic_stripe/use_store_currency')) {
            $convertedFinalPrice = $this->priceCurrency->convertAndRound(
                $product->getFinalPrice(),
                null,
                $currency
            );

            $price = $this->helper->getProductDataPrice(
                $product,
                $convertedFinalPrice,
                $shouldInclTax,
                $quote->getCustomerId(),
                $quote->getStore()->getStoreId()
            );
        } else {
            $price = $this->helper->getProductDataPrice(
                $product,
                $product->getFinalPrice(),
                $shouldInclTax,
                $quote->getCustomerId(),
                $quote->getStore()->getStoreId()
            );
        }

        // Append Current Product
        $productTotal = $this->helper->getAmountCents($price, $currency);
        $amount += $productTotal;

        $items[] = [
            'label' => $product->getName(),
            'amount' => $productTotal,
            'pending' => false
        ];

        return [
            'country' => $this->getCountry(),
            'currency' => strtolower($currency),
            'total' => [
                'label' => $this->getLabel(),
                'amount' => $amount,
                'pending' => true
            ],
            'displayItems' => $items,
            'requestPayerName' => true,
            'requestPayerEmail' => true,
            'requestPayerPhone' => true,
            'requestShipping' => $this->helper->shouldRequestShipping($quote, $product),
        ];
    }

    /**
     * Get Quote
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        $quote = $this->checkoutHelper->getCheckout()->getQuote();
        if (!$quote->getId()) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $quote = $objectManager->create('Magento\Checkout\Model\Session')->getQuote();
        }

        return $quote;
    }

    /**
     * Get Country Code
     * @return string
     */
    public function getCountry()
    {
        $countryCode = $this->getQuote()->getBillingAddress()->getCountryId();
        if (empty($countryCode)) {
            $countryCode = $this->helper->getDefaultCountry();
        }
        return $countryCode;
    }

    /**
     * Get Label
     * @return string
     */
    public function getLabel()
    {
        return $this->helper->getLabel($this->getQuote());
    }
}
