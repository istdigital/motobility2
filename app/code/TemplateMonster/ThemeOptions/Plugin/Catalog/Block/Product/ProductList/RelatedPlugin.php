<?php

namespace TemplateMonster\ThemeOptions\Plugin\Catalog\Block\Product\ProductList;

use \Magento\Catalog\Block\Product\ProductList\Related;
use \TemplateMonster\ThemeOptions\Helper\Data;

/**
 * Config edit plugin.
 *
 * @package TemplateMonster\ThemeOptions\Block\Product\ProductList
 */

class RelatedPlugin
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
     * After toHTML.
     *
     * @param Related $subject
     * @param string $result
     *
     * @return string
     */
    public function afterToHtml(Related $subject, $result)
    {
        return $this->_helper->isProductShowRelated() ? $result : '';
    }

    /**
     * Find out if some products can be easy added to cart
     *
     * @return bool
     */
    public function aroundCanItemsAddToCart(Related $subject, callable $proceed)
    {
        return $this->_helper->isProductShowRelatedCheckbox() ? $proceed() : false;
    }

}


