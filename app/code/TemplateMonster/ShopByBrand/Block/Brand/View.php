<?php

namespace TemplateMonster\ShopByBrand\Block\Brand;

use TemplateMonster\ShopByBrand\Helper\Data as ShopByBrandHelper;
use Magento\Catalog\Block\Category\View as CategoryView;
use Magento\Catalog\Helper\Category;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;

/**
 * Brand view block.
 *
 * @package TemplateMonster\ShopByBrand\Block\Brand
 */
class View extends CategoryView
{
    /**
     * @var ShopByBrandHelper
     */
    protected $_helper;

    /**
     * View constructor.
     *
     * @param ShopByBrandHelper $helper
     * @param Context           $context
     * @param Resolver          $layerResolver
     * @param Registry          $registry
     * @param Category          $categoryHelper
     * @param array             $data
     */
    public function __construct(
        ShopByBrandHelper $helper,
        Context $context,
        Resolver $layerResolver,
        Registry $registry,
        Category $categoryHelper,
        array $data = []
    ) {
        $this->_helper = $helper;
        parent::__construct(
            $context,
            $layerResolver,
            $registry,
            $categoryHelper,
            $data
        );
    }

    /**
     * @return string
     */
    public function getProductListHtml()
    {
        return $this->getChildHtml('brand_product_list');
    }

    /**
     * Retrieve current brand model object.
     *
     * @return \TemplateMonster\ShopByBrand\Model\Brand
     */
    public function getCurrentBrand()
    {
        return $this->_coreRegistry->registry('current_brand');
    }

    /**
     * Return identifiers for produced content.
     *
     * @return array
     */
    public function getIdentities()
    {
        return $this->getCurrentBrand()->getIdentities();
    }

    /**
     * Get helper instance.
     *
     * @return ShopByBrandHelper
     */
    public function getHelper()
    {
        return $this->_helper;
    }
}