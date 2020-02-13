<?php

namespace TemplateMonster\ThemeOptions\Plugin\Catalog\Block\Product\ProductList;

use \Magento\Catalog\Block\Product\ProductList\Upsell;
use \TemplateMonster\ThemeOptions\Helper\Data;

/**
 * Config edit plugin.
 *
 * @package TemplateMonster\ThemeOptions\Block\Product\ProductList
 */

class UpsellPlugin
{

    /**
     * Config sections.
     *
     * @var helper
     */
    protected $_helper;

    /**
     * Plugin constructor.
     *
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->_helper = $helper;
    }

    /**
     * Around get item limit.
     *
     * @param Upsell $subject
     * @param \Closure $proceed
     * @param string $type
     *
     * @return string
     */
    public function aroundGetItemLimit(Upsell $subject, \Closure $proceed, $type = '')
    {
        $limit = $this->_helper->getProductDetailUpsellLimit();
        return ($type == "upsell" && $limit != 0) ? $limit : $proceed($type);
    }

    /**
     * After toHTML.
     *
     * @param Upsell $subject
     * @param string $result
     *
     * @return string
     */
    public function afterToHtml(Upsell $subject, $result)
    {
        return $this->_helper->isShowProductDetailUpsell() ? $result : '';
    }
}


