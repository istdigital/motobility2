<?php

namespace Zendesk\Zendesk\Helper;

use Magento\Framework\App\Helper\Context;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    const DOMAIN_CONFIG_PATH = 'zendesk/general/domain';
    const AGENT_EMAIL_CONFIG_PATH = 'zendesk/general/email';
    const API_TOKEN_CONFIG_PATH = 'zendesk/general/password';
    const WEB_WIDGET_ENABLED_CONFIG_PATH = 'zendesk/frontend_features/web_widget_code_active';
    const ENABLE_DEBUG_LOGGING_CONFIG_PATH = 'zendesk/debug/enable_debug_logging';
    const WEB_WIDGET_DYNAMIC_SNIPPET_URL_PATTERN_CONFIG_PATH = 'zendesk/web_widget/dynamic_snippet_url_pattern';
    const WEB_WIDGET_CUSTOMIZE_URL_PATTERN_CONFIG_PATH = 'zendesk/web_widget/web_widget_customize_url';
    const ZENDESK_APP_ID_CONFIG_PATH = 'zendesk/zendesk_app/app_id';
    const ZENDESK_APP_CORS_ORIGIN_PATTERN_CONFIG_PATH = 'zendesk/zendesk_app/cors_origin_pattern';

    const ZENDESK_APP_DISPLAY_NAME_CONFIG_PATH = 'zendesk/zendesk_app/display_name';
    const ZENDESK_APP_DISPLAY_ORDER_STATUS_CONFIG_PATH = 'zendesk/zendesk_app/display_order_status';
    const ZENDESK_APP_DISPLAY_ORDER_STORE_CONFIG_PATH = 'zendesk/zendesk_app/display_order_store';
    const ZENDESK_APP_DISPLAY_ITEM_QUANTITY_CONFIG_PATH = 'zendesk/zendesk_app/display_item_quantity';
    const ZENDESK_APP_DISPLAY_ITEM_PRICE_CONFIG_PATH = 'zendesk/zendesk_app/display_item_price';
    const ZENDESK_APP_DISPLAY_TOTAL_PRICE_CONFIG_PATH = 'zendesk/zendesk_app/display_total_price';
    const ZENDESK_APP_DISPLAY_SHIPPING_ADDRESS_CONFIG_PATH = 'zendesk/zendesk_app/display_shipping_address';
    const ZENDESK_APP_DISPLAY_SHIPPING_METHOD_CONFIG_PATH = 'zendesk/zendesk_app/display_shipping_method';
    const ZENDESK_APP_DISPLAY_TRACKING_NUMBER_CONFIG_PATH = 'zendesk/zendesk_app/display_tracking_number';
    const ZENDESK_APP_DISPLAY_ORDER_COMMENTS_CONFIG_PATH = 'zendesk/zendesk_app/display_order_comments';

    const API_CREDENTIAL_PATHS = [
        \Zendesk\Zendesk\Helper\Config::AGENT_EMAIL_CONFIG_PATH,
        \Zendesk\Zendesk\Helper\Config::DOMAIN_CONFIG_PATH,
        \Zendesk\Zendesk\Helper\Config::API_TOKEN_CONFIG_PATH
    ];

    const DOMAIN_PLACEHOLDER = '{domain}';
    const ZENDESK_DOMAIN = '.zendesk.com';

    const BRAND_FIELD_CONFIG_PATH_PREFIX = 'brand-mapping-';
    const BRAND_FIELD_GROUP_PREFIX = 'zendesk/brand_mapping';

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $configWriter;
    /**
     * @var \Magento\Framework\App\Cache\Manager
     */
    protected $cacheManager;

    /**
     * Config constructor.
     * @param Context $context
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Framework\App\Cache\Manager $cacheManager
     */
    public function __construct(
        Context $context,
        // End parent parameters
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\App\Cache\Manager $cacheManager
    ) {
        parent::__construct($context);
        $this->configWriter = $configWriter;
        $this->cacheManager = $cacheManager;
    }

    /**
     * Get configured zendesk domain
     *
     * @param string $scopeType
     * @param ?string $scopeCode
     * @return string
     */
    public function getDomain($scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return (string)$this->scopeConfig->getValue(self::DOMAIN_CONFIG_PATH, $scopeType, $scopeCode);
    }

    /**
     * Get configured zendesk subdomain
     *
     * @param string $scopeType
     * @param ?string $scopeCode
     * @return string
     */
    public function getSubDomain(
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        $domain = $this->getDomain($scopeType, $scopeCode);

        return str_replace(self::ZENDESK_DOMAIN, '', $domain);
    }

    /**
     * Get configured zendesk agent email
     *
     * @param string $scopeType
     * @param ?string $scopeCode
     * @return string
     */
    public function getAgentEmail(
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return (string)$this->scopeConfig->getValue(self::AGENT_EMAIL_CONFIG_PATH, $scopeType, $scopeCode);
    }

    /**
     * Get configured zendesk API token
     *
     * @param string $scopeType
     * @param ?string $scopeCode
     * @return string
     */
    public function getApiToken(
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return (string)$this->scopeConfig->getValue(self::API_TOKEN_CONFIG_PATH, $scopeType, $scopeCode);
    }

    /**
     * Get if web widget enabled by store
     *
     * @param string $scopeType
     * @param null $scopeCode
     * @return bool
     */
    public function getWebWidgetEnabled(
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return (bool)$this->scopeConfig->getValue(self::WEB_WIDGET_ENABLED_CONFIG_PATH, $scopeType, $scopeCode);
    }

    /**
     * Get if API call debug info should be logged
     *
     * @return bool
     */
    public function getDebugLoggingEnabled()
    {
        return (bool)$this->scopeConfig->getValue(self::ENABLE_DEBUG_LOGGING_CONFIG_PATH);
    }

    /**
     * Get web widget dynamic snippet URL pattern.
     * Currently fixed value in config.xml, but could conceivably be updated in the future.
     *
     * Replace {domain} with full zendesk domain.
     *
     * @return string
     */
    public function getWebWidgetDynamicSnippetUrlPattern()
    {
        return (string)$this->scopeConfig->getValue(self::WEB_WIDGET_DYNAMIC_SNIPPET_URL_PATTERN_CONFIG_PATH);
    }

    /**
     * Get web widget customization URL pattern.
     * Currently fixed value in config.xml, but could conceivably be updated in the future.
     *
     * @return string
     */
    public function getWebWidgetCustomizeUrlPattern()
    {
        return (string)$this->scopeConfig->getValue(self::WEB_WIDGET_CUSTOMIZE_URL_PATTERN_CONFIG_PATH);
    }

    /**
     * Get corresponding Zendesk APP ID.
     * Currently fixed value in config.xml, but could conceivably be updated in the future.
     *
     * @return int
     */
    public function getZendeskAppId()
    {
        return (int)$this->scopeConfig->getValue(self::ZENDESK_APP_ID_CONFIG_PATH);
    }

    /**
     * Get store ID(s) mapped to given brand ID
     *
     * @param int $brandId
     * @return int[]
     */
    public function getBrandStores($brandId)
    {
        $stores = $this->scopeConfig->getValue(
            self::BRAND_FIELD_GROUP_PREFIX . '/' . self::BRAND_FIELD_CONFIG_PATH_PREFIX . $brandId
        );

        return explode(',', $stores);
    }

    /**
     * Set stores associated with a given brand
     *
     * @param int $brandId
     * @param array $storeIds
     */
    public function setBrandStores($brandId, array $storeIds)
    {
        $this->configWriter->save(
            self::BRAND_FIELD_GROUP_PREFIX . '/' . self::BRAND_FIELD_CONFIG_PATH_PREFIX . $brandId,
            implode(',', $storeIds)
        );

        // Newly set config value won't take effect unless config cache is cleaned.
        $this->cacheManager->clean([\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER]);
    }

    /**
     * Get regex pattern for valid CORS origins for Zendesk app.
     * Currently fixed value in config.xml, but could conceivably be updated in the future.
     *
     * @return string
     */
    public function getZendeskAppCorsOrigin()
    {
        return (string)$this->scopeConfig->getValue(self::ZENDESK_APP_CORS_ORIGIN_PATTERN_CONFIG_PATH);
    }

    /**
     * Get if Zendesk app should display customer name
     *
     * @param string $scopeType
     * @param null $scopeCode
     * @return bool
     */
    public function getZendeskAppDisplayName(
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return (bool)$this->scopeConfig->getValue(
            self::ZENDESK_APP_DISPLAY_NAME_CONFIG_PATH,
            $scopeType,
            $scopeCode
        );
    }

    /**
     * Get if Zendesk app should display order status
     *
     * @param string $scopeType
     * @param null $scopeCode
     * @return bool
     */
    public function getZendeskAppDisplayOrderStatus(
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return (bool)$this->scopeConfig->getValue(
            self::ZENDESK_APP_DISPLAY_ORDER_STATUS_CONFIG_PATH,
            $scopeType,
            $scopeCode
        );
    }

    /**
     * Get if Zendesk app should display order store
     *
     * @param string $scopeType
     * @param null $scopeCode
     * @return bool
     */
    public function getZendeskAppDisplayOrderStore(
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return (bool)$this->scopeConfig->getValue(
            self::ZENDESK_APP_DISPLAY_ORDER_STORE_CONFIG_PATH,
            $scopeType,
            $scopeCode
        );
    }

    /**
     * Get if Zendesk app should display item quantity
     *
     * @param string $scopeType
     * @param null $scopeCode
     * @return bool
     */
    public function getZendeskAppDisplayItemQuantity(
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return (bool)$this->scopeConfig->getValue(
            self::ZENDESK_APP_DISPLAY_ITEM_QUANTITY_CONFIG_PATH,
            $scopeType,
            $scopeCode
        );
    }

    /**
     * Get if Zendesk app should display item price
     *
     * @param string $scopeType
     * @param null $scopeCode
     * @return bool
     */
    public function getZendeskAppDisplayItemPrice(
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return (bool)$this->scopeConfig->getValue(
            self::ZENDESK_APP_DISPLAY_ITEM_PRICE_CONFIG_PATH,
            $scopeType,
            $scopeCode
        );
    }

    /**
     * Get if Zendesk app should display total price
     *
     * @param string $scopeType
     * @param null $scopeCode
     * @return bool
     */
    public function getZendeskAppDisplayTotalPrice(
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return (bool)$this->scopeConfig->getValue(
            self::ZENDESK_APP_DISPLAY_TOTAL_PRICE_CONFIG_PATH,
            $scopeType,
            $scopeCode
        );
    }

    /**
     * Get if Zendesk app should display shipping address
     *
     * @param string $scopeType
     * @param null $scopeCode
     * @return bool
     */
    public function getZendeskAppDisplayShippingAddress(
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return (bool)$this->scopeConfig->getValue(
            self::ZENDESK_APP_DISPLAY_SHIPPING_ADDRESS_CONFIG_PATH,
            $scopeType,
            $scopeCode
        );
    }

    /**
     * Get if Zendesk app should display shipping method
     *
     * @param string $scopeType
     * @param null $scopeCode
     * @return bool
     */
    public function getZendeskAppDisplayShippingMethod(
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return (bool)$this->scopeConfig->getValue(
            self::ZENDESK_APP_DISPLAY_SHIPPING_METHOD_CONFIG_PATH,
            $scopeType,
            $scopeCode
        );
    }

    /**
     * Get if Zendesk app should display tracking number(s)
     *
     * @param string $scopeType
     * @param null $scopeCode
     * @return bool
     */
    public function getZendeskAppDisplayTrackingNumber(
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return (bool)$this->scopeConfig->getValue(
            self::ZENDESK_APP_DISPLAY_TRACKING_NUMBER_CONFIG_PATH,
            $scopeType,
            $scopeCode
        );
    }

    /**
     * Get if Zendesk app should display order comments
     *
     * @param string $scopeType
     * @param null $scopeCode
     * @return bool
     */
    public function getZendeskAppDisplayOrderComments(
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return (bool)$this->scopeConfig->getValue(
            self::ZENDESK_APP_DISPLAY_ORDER_COMMENTS_CONFIG_PATH,
            $scopeType,
            $scopeCode
        );
    }
}
