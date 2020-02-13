<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\AjaxCatalog\Model\Source\Filterable;

class AttributeList implements \Magento\Framework\Option\ArrayInterface
{

    protected $_filterableAttributes;

    public function __construct(\Magento\Catalog\Model\Layer\Category\FilterableAttributeList $filterableAttributes)
    {
        $this->_filterableAttributes = $filterableAttributes;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $items = [];
        $filterableAttributes = $this->_filterableAttributes;
        $attributesList = $filterableAttributes->getList();

        foreach($attributesList as $item) {
            if(($item->getAttributeCode() == 'price')
                || ($item->getBackendType() == 'decimal')) {
                continue;
            }
            $items[] = ['value'=> $item->getAttributeCode(),  'label' => __($item->getFrontendLabel())];
        }
        return $items;
    }

}