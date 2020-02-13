<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
namespace Manadev\ProductCollection;

use Magento\Framework\App\RequestInterface;

class FilterGenerator
{
    /**
     * @var Factory
     */
    private $factory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Catalog\Model\Layer\Category\FilterableAttributeList
     */
    private $filterableAttributes;

    public function __construct(
        \Magento\Catalog\Model\Layer\Category\FilterableAttributeList $filterableAttributes,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Factory $factory
    ) {
        $this->storeManager = $storeManager;
        $this->factory = $factory;
        $this->filterableAttributes = $filterableAttributes;
    }

    public function getFilter($field, $data) {
        $filter = false;

        if($field == "category_ids") {
            return false;
        }

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $attributes */
        $attributes = $this->filterableAttributes->getList();
        $attribute = $attributes->getItemByColumnValue('attribute_code', $field);
        if(!$attribute){
            return false;
        }

        switch ($attribute->getFrontendInput()) {
            case "boolean":
            case "multiselect":
            case "select":
                $data = is_string($data) || is_int($data) ? [$data] : $data;
                $filter = $this->factory->createLayeredDropdownFilter($field, $attribute->getId(), $data);
                break;
            case "price":
                $data = [[$data['from'], $data['to']]];
                $filter = $this->factory->createLayeredPriceFilter($field, $attribute->getId(), $data);
                break;
        }

        return $filter;
    }
}