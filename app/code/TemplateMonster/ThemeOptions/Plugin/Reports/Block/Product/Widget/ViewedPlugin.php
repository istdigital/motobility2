<?php

namespace TemplateMonster\ThemeOptions\Plugin\Reports\Block\Product\Widget;

use \Magento\Reports\Block\Product\Widget\Viewed;
use \TemplateMonster\ThemeOptions\Helper\Data;

/**
 * Config edit plugin.
 *
 * @package TemplateMonster\ThemeOptions\Plugin\Block\Product\Viewed
 */
class ViewedPlugin
{
    /**
     * ThemeOptions helper
     *
     * @var helper
     */
    protected $_helper;

    /**
     * Construct
     *
     * @param \TemplateMonster\ThemeOptions\Helper\Data $helper
     *
     */
    public function __construct(
        Data $helper
    ) {
        $this->_helper = $helper;
    }

    /**
     * Show/hide Recently Viewed Block
     *
     * @return string
     *
     */
    public function aroundToHtml(Viewed $subject, callable $proceed)
    {
        return $this->_helper->isRecentlyViewed() ? $proceed() : false;
    }

}