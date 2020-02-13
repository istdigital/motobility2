<?php

namespace TemplateMonster\ThemeOptions\Plugin\Catalog\Block\Product;

use \Magento\Catalog\Block\Product\View;
use \TemplateMonster\ThemeOptions\Helper\Data;

/**
 * Config edit plugin.
 *
 * @package TemplateMonster\ThemeOptions\Plugin\Catalog\Block\Product
 */
class ViewPlugin
{
    /**
     * ThemeOptions helper.
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
     * Get product reviews summary
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function aroundGetReviewsSummaryHtml(
        View $subject,
        callable $proceed,
        \Magento\Catalog\Model\Product $product,
        $templateType = false,
        $displayIfNoReviews = false)
    {
        return $this->_helper->isProductShowReviews()
            ? $proceed($product, $templateType, $displayIfNoReviews)
            : '';
    }

    /**
     * Check if product can be emailed to friend
     *
     * @return bool
     */
    public function aroundCanEmailToFriend(View $subject, callable $proceed)
    {
        return $this->_helper->isProductShowEmailFiend() ? $proceed() : false;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function aroundToHtml(View $subject, callable $proceed)
    {
        $layout = $subject->getLayout();
        $this->_helper->getProductShowSku() ?: $layout->unsetElement('product.info.sku');
        $this->_helper->getProductShowShortDesc() ?: $layout->unsetElement('product.info.overview');
        return $proceed();
    }
}