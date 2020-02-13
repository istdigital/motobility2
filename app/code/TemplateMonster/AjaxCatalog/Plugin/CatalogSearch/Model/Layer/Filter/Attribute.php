<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\AjaxCatalog\Plugin\CatalogSearch\Model\Layer\Filter;

class Attribute
{

    protected $_multipleAttribute;
    protected $_helper;

    public function __construct(
        \TemplateMonster\AjaxCatalog\Model\CatalogSearch\Layer\Filter\MultipleAttributeFactory $multipleAttribute,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \TemplateMonster\AjaxCatalog\Helper\Catalog\View\ContentAjaxResponse $helper
    )
    {
        $this->_multipleAttribute = $multipleAttribute;
        $this->_catalogLayer = $layerResolver->get();
        $this->_helper = $helper;

    }

    public function afterGetFilters(\Magento\Catalog\Model\Layer\FilterList $subject,$result)
    {
        $multiAttrArr = $this->_helper->getMultiFilterAttributes();
        if(!$multiAttrArr || !is_array($multiAttrArr)) {
            return $result;
        }
        foreach($result as $key => $item) {
            if(in_array($item->getRequestVar(),$multiAttrArr)) {
                $filter = $this->_multipleAttribute->create(
                    ['data' => ['attribute_model' => $item->getAttributeModel()], 'layer' => $this->_catalogLayer]
                );
                $result[$key] =  $filter;
            }
        }
        return $result;
    }

}