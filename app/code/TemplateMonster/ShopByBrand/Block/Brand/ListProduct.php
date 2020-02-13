<?php

namespace TemplateMonster\ShopByBrand\Block\Brand;

/**
 * Brand view block.
 *
 * @package TemplateMonster\ShopByBrand\Block\Brand
 */
class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
{
	protected function _getProductCollection()
    {
        if ($this->_productCollection === null) {
            $this->_productCollection = $this->initializeProductCollection();
        }
        $brand = $this->_coreRegistry->registry('current_brand');

        return $this->_productCollection->addAttributeToFilter('brand_id',$brand->getBrandId());
    }

    private function initializeProductCollection()
    {
        $layer = $this->getLayer();
        /* @var $layer Layer */
        if ($this->getShowRootCategory()) {
            $this->setCategoryId($this->_storeManager->getStore()->getRootCategoryId());
        }
        $collection = $layer->getProductCollection();

        $this->prepareSortableFieldsByCategory($layer->getCurrentCategory());



        return $collection;
    }
    private function addToolbarBlock(Collection $collection)
    {
        $toolbarLayout = $this->getToolbarFromLayout();

        if ($toolbarLayout) {
            $this->configureToolbar($toolbarLayout, $collection);
        }
    }
}