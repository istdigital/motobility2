<?php

namespace TemplateMonster\AjaxCatalog\Plugin\Catalog\Model\ResourceModel\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;

/**
 * Class Collection
 *
 * @package TemplateMonster\AjaxCatalog\Plugin\Catalog\Model\ResourceModel\Product
 */
class Collection
{
    /**
     * @param ProductCollection $subject
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute|string $attribute
     * @param array $condition
     * @param string $joinType
     *
     * @return array
     */
    public function beforeAddAttributeToFilter(ProductCollection $subject, $attribute, $condition = null, $joinType = 'inner')
    {
        if ($this->_isEqArrayCondition($condition)) {
            $condition['in'] = $condition['eq'];
            unset($condition['eq']);
        }

        return [$attribute, $condition, $joinType];
    }

    /**
     * Check is equal array condition.
     *
     * @param mixed $condition
     *
     * @return bool
     */
    protected function _isEqArrayCondition($condition)
    {
        return isset($condition['eq']) && is_array($condition['eq']);
    }
}