<?php

namespace TemplateMonster\ThemeOptions\Plugin\Catalog\Block\Product\View;

use \Magento\Catalog\Block\Product\View\Description;
use \TemplateMonster\ThemeOptions\Helper\Data;

/**
 * Config edit plugin.
 *
 * @package TemplateMonster\ThemeOptions\Plugin\Catalog\Block\Product\View
 */
class DescriptionPlugin
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
     * Render block HTML
     *
     * @return string
     */
    public function aroundToHtml(Description $subject, callable $proceed)
    {
        $layout = $subject->getLayout();
        $descTitle = $this->_helper->getProductTabsDescTitle();
        $additionalTitle = $this->_helper->getProductTabsAdditionalTitle();
        $reviewsTitle = $this->_helper->getProductTabsReviewTitle();
        $enabledBlocks = [
            'product.info.description' => $this->_helper->getProductTabsDesc(),
            'product.attributes' => $this->_helper->getProductTabsAdditional(),
            'reviews.tab' => $this->_helper->getProductTabsReview()
        ];
        foreach($layout->getChildNames('product.info.details') as $name){
            $block = $layout->getBlock($name);
            if($name == 'product.info.description' && $descTitle){
                $block->setTitle($descTitle);
            }
            if($name == 'product.attributes' && $additionalTitle){
                $block->setTitle($additionalTitle);
            }
            if($name == 'reviews.tab' && $reviewsTitle){
                $block->setTitle($reviewsTitle);
            }
            if (isset($enabledBlocks[$name]) && !$enabledBlocks[$name]) {
                $layout->unsetElement($name);
            }
        }
        return $proceed();
    }

}