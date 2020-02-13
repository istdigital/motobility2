<?php

namespace TemplateMonster\LayoutSwitcher\Model\Config\Source;

use Magento\Store\Model\ResourceModel\Store\Collection;

/**
 * Store source model.
 *
 * @package TemplateMonster\LayoutSwitcher\Model\Config\Source
 */
class Store extends Collection
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('code', 'name', ['website_id' => 'website_id']);
    }
}