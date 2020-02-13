<?php

namespace TemplateMonster\LayoutSwitcher\Block;

use TemplateMonster\LayoutSwitcher\Helper\Data as LayoutSwitcherHelper;
use TemplateMonster\LayoutSwitcher\Model\Config\Source\Website as WebsiteSource;
use TemplateMonster\LayoutSwitcher\Model\Config\Source\Store as StoreSource;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Json\Helper\Data as JsonHelper;

/**
 * Switcher frontend block.
 *
 * @package TemplateMonster\LayoutSwitcher\Block
 */
class Switcher extends AbstractBlock
{
    /**
     * @var string
     */
    protected $_template = 'switcher.phtml';

    /**
     * @var WebsiteSource
     */
    protected $_websiteSource;

    /**
     * @var StoreSource
     */
    protected $_storeSource;

    /**
     * @var JsonHelper
     */
    protected $_jsonHelper;

    /**
     * @var JsonHelper
     */
    protected $_actionUrl;

    /**
     * Switcher constructor.
     *
     * @param WebsiteSource        $websiteSource
     * @param StoreSource          $storeSource
     * @param LayoutSwitcherHelper $helper
     * @param JsonHelper           $jsonHelper
     * @param Template\Context     $context
     * @param bool                 $livedemoMode
     * @param array                $data
     */
    public function __construct(
        WebsiteSource $websiteSource,
        StoreSource $storeSource,
        LayoutSwitcherHelper $helper,
        JsonHelper $jsonHelper,
        Template\Context $context,
        $livedemoMode = false,
        array $data = []
    )
    {
        $this->_websiteSource = $websiteSource;
        $this->_storeSource = $storeSource;
        $this->_jsonHelper = $jsonHelper;
        $this->_actionUrl = 'layoutswitcher/index/index';
        parent::__construct($helper, $context, $data);
    }

    /**
     * Get form action url.
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->getUrl($this->_actionUrl);
    }

    /**
     * Get form action url.
     *
     * @return string
     */
    public function getActionUrl()
    {
        return $this->_actionUrl;
    }


    /**
     * Get reset post action.
     *
     * @return string
     */
    public function getResetPostAction()
    {
        return $this->_helper->getResetPostAction();
    }

    /**
     * Get theme options.
     *
     * @return array
     */
    public function getThemeOptions()
    {
        return $this->_websiteSource->toOptionArray();
    }

    /**
     * Get homepage options.
     *
     * @return array
     */
    public function getHomepageOptions()
    {
        return $this->_storeSource->toOptionArray();
    }

    /**
     * Get layout types.
     *
     * @return array
     */
    public function getLayoutTypes()
    {
        return $this->_helper->getLayoutTypes();
    }

    /**
     * Get layout options.
     *
     * @param string $type
     *
     * @return array
     */
    public function getLayoutOptions($type)
    {
        return $this->_helper->getLayouts($type);
    }

    /**
     * Check is current theme.
     *
     * @param string $theme
     *
     * @return bool
     */
    public function isCurrentTheme($theme)
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->_storeManager->getStore();

        return $theme === $store->getWebsite()->getCode();
    }

    /**
     * Check is current homepage.
     *
     * @param string $homepage
     *
     * @return bool
     */
    public function isCurrentHomepage($homepage)
    {
        return $homepage === $this->_storeManager->getStore()->getCode();
    }

    /**
     * Check is current layout.
     *
     * @param string $type
     * @param string $layout
     *
     * @return bool
     */
    public function isCurrentLayout($type, $layout)
    {
        return $layout === $this->_helper->getCookieValue($type);
    }

    /**
     * Get json theme default layouts.
     *
     * @return string
     */
    public function getThemeDefaultLayoutsJson()
    {
        return $this->_jsonHelper->jsonEncode($this->getThemeDefaultLayouts());
    }

    /**
     * Get theme default layouts.
     *
     * @return array
     */
    public function getThemeDefaultLayouts()
    {
        $websites = $this->_storeManager->getWebsites();
        $types = $this->_helper->getLayoutTypes();

        $layouts = [];
        foreach ($websites as $website) {
            $layouts[$website->getCode()] = [];
            foreach ($types as $type) {
                $layouts[$website->getCode()][$type] = $this->_helper->getDefaultLayout($type, $website);
            }
        }

        return $layouts;
    }

    /**
     * Get BaseUrl by website Id.
     *
     * @param int $websiteId
     * @return array
     */

    public function getBaseUrlByWebsiteId($websiteId)
    {
        return $websiteId ? $this->_storeManager->getWebsite($websiteId)->getDefaultStore()->getBaseUrl() : $this->getBaseUrl();
    }
}