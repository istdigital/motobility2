<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation\Plugins;

use Manadev\LayeredNavigation\Factory;
use Manadev\LayeredNavigation\Models\Filter;
use Magento\Store\Model\StoreManagerInterface;

class ProductAttributesBlockPlugin
{
    /**
     * @var Filter[]
     */
    protected $filters;

    /**
     * @var Factory
     */
    protected $factory;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(Factory $factory, StoreManagerInterface $storeManager) {
        $this->factory = $factory;
        $this->storeManager = $storeManager;
    }

    public function afterGetAdditionalData(\Magento\Catalog\Block\Product\View\Attributes $block, $data) {
        $product = $block->getProduct();
        $attributes = $product->getAttributes();
        foreach ($attributes as $attribute) {
            if (!isset($data[$attribute->getAttributeCode()])) {
                continue;
            }

            if (!$product->hasData($attribute->getAttributeCode())) {
                continue;
            }

            if (!($value = $attribute->getFrontend()->getValue($product))) {
                continue;
            }

            if (!($attribute->getFrontendInput() == 'price' && is_string($value))) {
                continue;
            }

            if (!($filter = $this->getFilter($attribute->getId()))) {
                continue;
            }

            $data[$attribute->getAttributeCode()]['value'] = $this->format($filter, $value);
        }

        return $data;
    }

    protected function getFilter($attributeId) {
        return isset($this->getFilters()[$attributeId]) ? $this->getFilters()[$attributeId] : null;
    }
    protected function getFilters() {
        if (!$this->filters) {
            $storeId = $this->storeManager->getStore()->getId();
            $filters = $this->factory->createFilterCollection()
                ->storeSpecific($storeId)
                ->addFieldToFilter('type', 'decimal');

            $this->filters = [];
            foreach ($filters as $filter) {
                /* @var Filter $filter */
                $this->filters[$filter->getData('attribute_id')] = $filter;
            }
        }

        return $this->filters;
    }

    protected function format(Filter $filter, $value) {
        $value = (float)$value;
        $prefix = '';

        if ($filter->getData('is_two_number_formats') &&
            $value >= $filter->getData('use_second_number_format_on'))
        {
            $value /= $filter->getData('use_second_number_format_on');
            $prefix = 'second_';
        }

        $format = $filter->getData($prefix . 'number_format');
        $precision = $filter->getData($prefix . "decimal_digits");

        $value = round($value, $precision);

        return str_replace('0', (string)$value ?: '0', $format);
    }

}