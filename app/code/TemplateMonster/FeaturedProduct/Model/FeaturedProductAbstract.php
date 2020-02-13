<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FeaturedProduct\Model;

abstract class FeaturedProductAbstract implements \TemplateMonster\FeaturedProduct\Api\FeaturedProductInterface
{

    /**
     * Catalog config
     *
     * @var \Magento\Catalog\Model\Config
     */
    protected $_catalogConfig;

    /**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * Category factory
     *
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * Product collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    public function __construct(
        \Magento\Reports\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localDate
    )
    {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_catalogConfig = $catalogConfig;
        $this->_categoryFactory = $categoryFactory;
        $this->_localeDate = $localDate;
    }

    /**
     * Predefined collection
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function _getProductCollection($categoryId = false)
    {
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_productCollectionFactory->create();
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());

        $collection = $this->_addProductAttributesAndPrices($collection)->addStoreFilter();

        if($categoryId){
            /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $categories */
            $collection->addCategoriesFilter(['eq'  => $categoryId]);
            $collection->addAttributeToSort('category_id');
        }

        return $collection;
    }

    /**
     * Add all attributes and apply pricing logic to products collection
     * to get correct values in different products lists.
     * E.g. crosssells, upsells, new products, recently viewed
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function _addProductAttributesAndPrices(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
    ) {
        return $collection
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
            ->addUrlRewrite();
    }

    /**
     *
     * Return prepared collection
     *
     * @param $collectionSize
     * @param array $productIds
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getPreparedCollection($collectionSize,$productIds=[],$categories=false)
    {
        $productCollection =  $this->_getProductCollection($categories);
        $this->getFeature($productCollection,$collectionSize);
        $productCollectionIds = $productCollection->getAllIds();
        if($productIds)
        {
            $this->_addAdditionalProducts($productCollection,$productIds,$productCollectionIds);
        }
        return $productCollection;
    }

    /**
     *
     *  Add additional items to collections
     *
     * @param $productCollection
     * @param $productIds
     * @param $productCollectionIds
     * @return mixed
     */
    protected function _addAdditionalProducts($productCollection,$productIds,$productCollectionIds)
    {
        $additionalIds = array_diff($productIds,$productCollectionIds);
        if(!$additionalIds) {
            return $productCollection;
        }
        $productCollectionByIds =  $this->_getProductCollection();
        $productCollectionByIds->addFieldToFilter('entity_id', $additionalIds);

        foreach($productCollectionByIds->getItems() as $item) {
            $productCollection->addItem($item);
        }
        return $productCollection;
    }

}