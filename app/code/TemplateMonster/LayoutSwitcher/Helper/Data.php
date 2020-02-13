<?php

namespace TemplateMonster\LayoutSwitcher\Helper;

use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Config\Reader\Filesystem;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Data helper.
 *
 * @package TemplateMonster\LayoutSwitcher\Helper
 */
class Data extends AbstractHelper
{
    /**
     * XML module prefix.
     */
    const XML_MODULE_PREFIX = 'layout_switcher/';

    /**
     * Enabled config option.
     */
    const XML_PATH_ENABLED = self::XML_MODULE_PREFIX . 'general/enabled';
	
	/**
     * Enabled frontend option.
     */
    const XML_PATH_FRONTEND_PANEL = self::XML_MODULE_PREFIX . 'general/frontend_panel';

    /**
     * Default theme config option.
     */
    const XML_PATH_DEFAULT_THEME = self::XML_MODULE_PREFIX . 'general/default_theme';

    /**
     * Default theme config option.
     */
    const XML_PATH_DEFAULT_HOMEPAGE = self::XML_MODULE_PREFIX . 'general/default_homepage';

    /**
     * Default layout type config path.
     */
    const XML_PATH_DEFAULT_LAYOUT = self::XML_MODULE_PREFIX . 'general/default_%s_layout';

    /**
     * Default header layout config option.
     */
    const XML_PATH_DEFAULT_HEADER_LAYOUT = self::XML_MODULE_PREFIX . 'general/default_header_layout';

    /**
     * Default footer layout config option.
     */
    const XML_PATH_DEFAULT_FOOTER_LAYOUT = self::XML_MODULE_PREFIX . 'general/default_footer_layout';

    /**
     * Layout context name.
     */
    const LAYOUT_CONTEXT_NAME = 'current_layout';

    /**
     * Color scheme context name.
     */
    const COLOR_SCHEME_CONTEXT_NAME = 'color_scheme';

    /**
     * Livedemo init param.
     */
    const PARAM_MODE = 'LIVEDEMO_MODE';

    /**
     * @var StoreCookieManagerInterface
     */
    protected $_storeCookieManager;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var CookieMetadataFactory
     */
    protected $_cookieMetadataFactory;

    /**
     * @var CookieManagerInterface
     */
    protected $_cookieManager;

    /**
     * @var HttpContext
     */
    protected $_httpContext;

    /**
     * @var PostHelper
     */
    protected $_postHelper;

    /**
     * @var Filesystem
     */
    protected $_reader;

    /**
     * @var array
     */
    private $_cookieCache = [];

    /**
     * @var null
     */
    private $_theme = null;

    /**
     * @var string|null
     */
    private $_store = null;

    /**
     * Data constructor.
     *
     * @param StoreCookieManagerInterface $storeCookieManager
     * @param StoreManagerInterface       $storeManager
     * @param CookieMetadataFactory       $cookieMetadataFactory
     * @param CookieManagerInterface      $cookieManager
     * @param HttpContext                 $httpContext
     * @param PostHelper                  $postHelper
     * @param Filesystem                  $reader
     * @param Context                     $context
     */
    public function __construct(
        StoreCookieManagerInterface $storeCookieManager,
        StoreManagerInterface $storeManager,
        CookieMetadataFactory $cookieMetadataFactory,
        CookieManagerInterface $cookieManager,
        HttpContext $httpContext,
        PostHelper $postHelper,
        Filesystem $reader,
        Context $context
    )
    {
        $this->_storeCookieManager = $storeCookieManager;
        $this->_storeManager = $storeManager;
        $this->_cookieMetadataFactory = $cookieMetadataFactory;
        $this->_cookieManager = $cookieManager;
        $this->_httpContext = $httpContext;
        $this->_postHelper = $postHelper;
        $this->_reader = $reader;
        parent::__construct($context);
    }

    /**
     * Check is enabled.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED);
    }
	
	/**
     * Check is enabled.
     *
     * @return bool
     */
    public function isFrontendPanel()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_FRONTEND_PANEL);
    }

    /**
     * Get default layout by type.
     *
     * @param string      $type
     * @param string|null $website
     *
     * @return mixed
     */
    public function getDefaultLayout($type, $website = null)
    {
        return $this->scopeConfig->getValue(
            sprintf(self::XML_PATH_DEFAULT_LAYOUT, $type),
            ScopeInterface::SCOPE_WEBSITE,
            $website
        );
    }

    /**
     * Get default theme.
     **
     * @return mixed
     */
    public function getDefaultTheme()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_DEFAULT_THEME);
    }

    /**
     * Get default theme.
     *
     * @return mixed
     */
    public function getDefaultHomepage()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_DEFAULT_HOMEPAGE);
    }

    /**
     * Get color schemes.
     *
     * @return array
     */
    public function getColorSchemes()
    {
        return [];
    }

    /**
     * Get default color scheme.
     *
     * @param string|null $theme
     *
     * @return string
     */
    public function getDefaultColorScheme($theme = null)
    {
        return null;
    }

    /**
     * Get layout types.
     *
     * @return array
     */
    public function getLayoutTypes()
    {
        $types = [];
        foreach ($this->_reader->read() as $data) {
            if (!in_array($data['type'], $types, true)) {
                $types[] = $data['type'];
            }
        }

        return $types;
    }

    /**
     * Get layouts.
     *
     * @param string|null $type
     *
     * @return array
     */
    public function getLayouts($type = null)
    {
        $layouts = [];
        foreach ($this->_reader->read() as $id => $data) {
            if ($type && $type !== $data['type']) {
                continue;
            }
            $layouts[$id] = $data;
        }

        return $layouts;
    }

    /**
     * Get layout metadata.
     *
     * @param string $id
     *
     * @return array|null
     */
    public function getLayoutMetadata($id)
    {
        $layouts = $this->getLayouts();

        return isset($layouts[$id]) ? $layouts[$id] : null;
    }

    /**
     * Get current layout.
     *
     * @param string $type
     *
     * @return mixed
     */
    public function getCurrentLayout($type)
    {
        return
            $this->getCookieValue($type)
                ?: $this->getDefaultLayout($type);
    }

    /**
     * Get current color scheme.
     *
     * @param string|null $theme
     *
     * @return string
     */
    public function getCurrentColorScheme($theme = null)
    {
        $name = sprintf('%s_color_scheme', $theme ?: $this->getCurrentTheme());

        return
            $this->getCookieValue($name)
                ?: $this->getDefaultColorScheme($theme);
    }

    /**
     * Set current theme.
     *
     * @param string $theme
     *
     * @return $this
     */
    public function setCurrentTheme($theme)
    {
        $this->_theme = $theme;

        return $this;
    }

    /**
     * Get current theme.
     *
     * @return string
     */
    public function getCurrentTheme()
    {
        if ($this->_theme !== null) {
            return $this->_theme;
        }

        if ($code = $this->_storeCookieManager->getStoreCodeFromCookie()) {
            /** @var \Magento\Store\Model\Store $store */
            $store = $this->_storeManager->getStore($code);

            return $store->getWebsite()->getCode();
        }

        return $this->getDefaultTheme();
    }

    /**
     * Get default layouts.
     *
     * @return array
     */
    public function getDefaultLayouts()
    {
        $layouts = [];
        foreach ($this->getLayoutTypes() as $type) {
            $layouts[$type] = $this->getDefaultLayout($type);
        }

        return $layouts;
    }

    /**
     * Get layout handles.
     *
     * @return array
     */
    public function getLayoutHandles()
    {
        $handles = [];
        foreach ($this->getLayoutTypes() as $type) {
            $id = $this->getCurrentLayout($type);
            $handles[$id] = $this->getLayoutMetadata($id);
        }

        return $handles;
    }

    /**
     * Set HTTP-context.
     *
     * @return $this
     */
    public function setHttpContext()
    {
        return $this
            ->setColorSchemeContext()
            ->setStoreContext()
            ->setLayoutContext();
    }

    /**
     * @param string|array $type
     * @param null|string $layout
     *
     * @return $this
     */
    public function setCookieLayout($type, $layout = null)
    {
        if ($type !== (array) $type) {
            $type = [$type => $layout];
        }

        foreach ($type as $name => $value) {
            $cookieMetadata = $this->_cookieMetadataFactory->createPublicCookieMetadata()
                ->setHttpOnly(true)
                ->setDurationOneYear()
                ->setPath('/');
            $this->_cookieManager->setPublicCookie($name, $value, $cookieMetadata);
            $this->_cookieCache[$name] = $value;
        }

        $this->setLayoutContext();

        return $this;
    }

    /**
     * Set cookie store.
     *
     * @param string $code
     *
     * @return $this
     */
    public function setCookieStore($code)
    {
        /** @var \Magento\Store\Model\Website $website */
//        $website = $this->_storeManager->getWebsite($websiteCode);
//        $store = $website->getDefaultStore();

        $store = $this->_storeManager->getStore($code);

        $this->_storeCookieManager->setStoreCookie($store);
        $this->_store = $code;

        $this->setStoreContext();

        return $this;
    }

    /**
     * Set cookie color scheme.
     *
     * @param string $colorScheme
     *
     * @return $this
     */
    public function setCookieColorScheme($colorScheme)
    {
        $cookieMetadata = $this->_cookieMetadataFactory->createPublicCookieMetadata()
            ->setHttpOnly(true)
            ->setDurationOneYear()
            ->setPath('/');

        $name = sprintf('%s_color_scheme', $this->getCurrentTheme());
        $this->_cookieManager->setPublicCookie($name, $colorScheme, $cookieMetadata);
        $this->_cookieCache[$name] = $colorScheme;

        $this->setColorSchemeContext();

        return $this;
    }

    /**
     * Set color scheme HTTP-context.
     *
     * @return $this
     */
    public function setColorSchemeContext()
    {
        $this->_httpContext->setValue(
            self::COLOR_SCHEME_CONTEXT_NAME,
            $this->getCurrentColorScheme(),
            $this->getDefaultColorScheme() ?: $this->getCurrentColorScheme()
        );

        return $this;
    }

    /**
     * Set store context.
     *
     * @return $this
     */
    public function setStoreContext()
    {
        $defaultStoreCode = $this->getDefaultTheme();
        $currentStoreCode = $this->_store ?: $this->_storeCookieManager->getStoreCodeFromCookie();
        $this->_httpContext->setValue(Store::ENTITY, $currentStoreCode, $defaultStoreCode);

        return $this;
    }

    /**
     * Get value from cookie.
     *
     * @param string $name
     *
     * @return string|null
     */
    public function getCookieValue($name)
    {
        if (!$this->isFrontendPanel()) {
            return null;
        }
        
        return isset($this->_cookieCache[$name])
            ? $this->_cookieCache[$name] :
            $this->_cookieManager->getCookie($name);
    }

    /**
     * Set layout http context.
     *
     * @return $this
     */
    public function setLayoutContext()
    {
        $this->_httpContext->setValue(
            self::LAYOUT_CONTEXT_NAME,
            $this->_getCurrentLayoutsHash(),
            $this->_getDefaultLayoutsHash()
        );

        return $this;
    }

    /**
     * Check if theme was changed.
     *
     * @return bool
     */
    public function isThemeChanged()
    {
        $current = $this->_storeManager->getWebsite();
        $new = $this->_getNewWebsite();

        return $current->getCode() !== $new->getCode();
    }

    /**
     * Get reset post action.
     *
     * @return string
     */
    public function getResetPostAction()
    {
        return $this->_postHelper->getPostData($this->getResetUrl());
    }

    /**
     * Get reset url.
     *
     * @return string
     */
    public function getResetUrl()
    {
        return $this->_urlBuilder->getUrl('layoutswitcher/index/reset');
    }

    /**
     * Get current layout hash.
     *
     * @return string
     */
    protected function _getCurrentLayoutsHash()
    {
        return md5(serialize(array_keys($this->getLayoutHandles())));
    }

    /**
     * Get default layouts hash.
     *
     * @return string
     */
    protected function _getDefaultLayoutsHash()
    {
        return md5(serialize($this->getDefaultLayouts()));
    }

    /**
     * Get new website.
     *
     * @return bool|\Magento\Store\Model\Website
     */
    protected function _getNewWebsite()
    {
        $code = $this->_store ?: $this->_storeCookieManager->getStoreCodeFromCookie();
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->_storeManager->getStore($code);

        return $store->getWebsite();
    }
}