<?php

namespace TemplateMonster\LayoutSwitcher\Model\Config\Source;

use Magento\Store\Model\ResourceModel\Website\Collection;

/**
 * Website source model.
 *
 * @package TemplateMonster\LayoutSwitcher\Model\Config\Source
 */
class Website extends Collection
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('code', 'name', ['website_id' => 'website_id']);
    }
}