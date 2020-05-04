<?php

namespace StripeIntegration\Payments\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use StripeIntegration\Payments\Helper\Logger;
use Magento\Store\Model\ScopeInterface;

class SepaConfigProvider implements ConfigProviderInterface
{

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var PaymentHelper
     */
    protected $_paymentHelper;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface $config
     */
    protected $_config;

    /**
     * @var \Magento\Payment\Model\Method\AbstractMethod[]
     */
    protected $methods = [];

    /**
     * @param \Magento\Framework\Model\Context                   $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param PaymentHelper                                      $paymentHelper
     * @param \Magento\Framework\Locale\ResolverInterface        $localeResolver
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        PaymentHelper $paymentHelper,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \StripeIntegration\Payments\Helper\Generic $helper,
        \StripeIntegration\Payments\Model\Config $config
    ) {
        $this->_appState = $context->getAppState();
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_storeManager = $storeManager;
        $this->_paymentHelper = $paymentHelper;
        $this->_localeResolver = $localeResolver;
        $this->_scopeConfig = $scopeConfig;
        $this->_helper = $helper;
        $this->_config = $config;
    }


    public function getConfig()
    {
        // Get Stripe Account info
        try
        {
            $storeId = $this->_helper->getStoreId();
            $businessName = $this->_scopeConfig->getValue("payment/stripe_payments_sepa/business_name", ScopeInterface::SCOPE_STORE, $storeId);

            if (empty($businessName))
            {
                $account = \Stripe\Account::retrieve();

                if (empty($account->business_name))
                    $businessName = $this->_storeManager->getStore()->getName();
                else
                    $businessName = $account->business_name;

                if (!empty($businessName))
                    $this->_config->setConfigData("business_name", $businessName, "sepa");
            }
        }
        catch (\Exception $e)
        {
            $businessName = "Our Business";
        }

        $config = [
            'payment' => [
                \StripeIntegration\Payments\Model\Method\Sepa::METHOD_CODE => [
                    'iban' => '',
                    'company' => $businessName
                ],
            ]
        ];

        return $config;
    }
}
