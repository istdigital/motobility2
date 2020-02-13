<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\AjaxCatalog\Model\CatalogSearch\Layer\Filter;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;

class MultipleAttribute extends AbstractFilter
{
    /**
     * @var \Magento\Framework\Filter\StripTags
     */
    private $tagFilter;


    private $request;

    /**
     * @param \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Layer $layer
     * @param \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder
     * @param \Magento\Framework\Filter\StripTags $tagFilter
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Framework\Filter\StripTags $tagFilter,
        \Magento\Framework\App\RequestInterface  $request,
        array $data = []
    )
    {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $data
        );
        $this->tagFilter = $tagFilter;
        $this->request = $request;
    }

    /**
     * Apply attribute option filter to product collection
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $attributeValue = $request->getParam($this->_requestVar);
        if (empty($attributeValue)) {
            return $this;
        }

        $attribute = $this->getAttributeModel();
        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()
            ->getProductCollection();

        $this->applyFilterType($productCollection,$attributeValue,$attribute);

        $attributeValue = is_array($attributeValue) ? $attributeValue : [$attributeValue];
        foreach($attributeValue as $item) {
            $label = $this->getOptionText($item);
            $this->getLayer()
                ->getState()
                ->addFilter($this->_createItem($label, $item));
        }

        //$this->setItems([]); // set items to disable show filtering
        return $this;
    }

    /**
     * Get data array for building attribute filter items
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getItemsData()
    {

        $request = $this->request;
        $currentFilterValue = $request->getParam($this->_requestVar);

        $attribute = $this->getAttributeModel();
        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()
            ->getProductCollection();
        $optionsFacetedData = $productCollection->getFacetedData($attribute->getAttributeCode());
        $productSize = $productCollection->getSize();

        $options = $attribute->getFrontend()
            ->getSelectOptions();
        foreach ($options as $option) {
            if (empty($option['value'])) {
                continue;
            }
            // Check filter type and skip if already applied
            if (empty($optionsFacetedData[$option['value']]['count'])
                || $this->filterValueIsApplied($currentFilterValue,$option['value'])
               ) {
                continue;
            }

            $this->itemDataBuilder->addItemData(
                $this->tagFilter->filter($option['label']),
                $this->createFilterUrl($option['value'],$currentFilterValue),
                $optionsFacetedData[$option['value']]['count']
            );
        }

        return $this->itemDataBuilder->build();
    }

    /**
     * Check if  current value already has been applied
     *
     * @param $filterValue
     * @param $value
     * @return bool
     */
    public function filterValueIsApplied($filterValue,$value) {
        if(is_array($filterValue)) {
            return in_array($value,$filterValue);
        } elseif(!empty($filterValue)) {
            return $filterValue == $value;
        }
        return false;
    }

    /**
     *
     * Check if current attribute already has been applied.
     * And merge values for new filter.
     *
     * @param $optionsValue
     * @param $currentFilterValue
     * @return array
     */
    public function createFilterUrl($optionsValue,$currentFilterValue)
    {
        $result = $optionsValue;

        if (is_array($currentFilterValue)) {
            $result = array_merge($currentFilterValue, [$optionsValue]);
        } elseif(!empty($currentFilterValue)) {
            $result = [$currentFilterValue, $optionsValue];
        }

        return $result;
    }

    /**
     *
     * Apply filter to collection
     * with AND or OR SQL conditions
     *
     * @param $productCollection
     * @param $attributeValue
     * @param $attribute
     * @param string $condType
     */
    public function applyFilterType($productCollection,$attributeValue,$attribute,$condType='OR')
    {
        $attributeValue = is_array($attributeValue) ? $attributeValue : [$attributeValue];

        if(strtoupper($condType) == \Zend_Db_Select::SQL_OR) {
            $productCollection->addFieldToFilter($attribute->getAttributeCode(), ['in'=>$attributeValue]);
        } else {
            foreach($attributeValue as $val) {
                $productCollection->addFieldToFilter($attribute->getAttributeCode(), $val);
            }
        }

    }
}