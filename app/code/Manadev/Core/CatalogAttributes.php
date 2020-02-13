<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;

class CatalogAttributes
{
    /**
     * @var Attribute[]
     */
    protected $attributes = [];

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * FilterableAttributeList constructor
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $collectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $collectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
    }


    public function get($attributeIds) {
        $isResultAnArray = true;
        if (!is_array($attributeIds)) {
            $attributeIds = [$attributeIds];
            $isResultAnArray = false;
        }

        $attributeIdsToLoad = $attributeIds;
        foreach (array_keys($attributeIdsToLoad) as $i) {
            if (isset($this->attributes[$attributeIdsToLoad[$i]])) {
                unset($attributeIdsToLoad[$i]);
            }
        }

        if (count($attributeIdsToLoad)) {
            /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection */
            $collection = $this->collectionFactory->create();
            $collection->setItemObjectClass('Magento\Catalog\Model\ResourceModel\Eav\Attribute')
                ->addStoreLabel($this->storeManager->getStore()->getId())
                ->addFieldToFilter('main_table.attribute_id', ['in' => $attributeIdsToLoad]);
            foreach ($collection as $attribute) {
                /* @var $attribute Attribute */
                $this->attributes[$attribute->getId()]= $attribute;
            }
        }

        if ($isResultAnArray) {
            $result = array();
            foreach ($attributeIds as $attributeId) {
                $result[$attributeId] = $this->attributes[$attributeId];
            }

            return $result;
        }
        else {
            return $this->attributes[$attributeIds[0]];
        }
    }
}