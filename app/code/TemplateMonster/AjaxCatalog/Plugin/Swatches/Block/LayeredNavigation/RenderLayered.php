<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\AjaxCatalog\Plugin\Swatches\Block\LayeredNavigation;

use Magento\Framework\App\RequestInterface;

class RenderLayered
{

    protected $_helper;

    public function __construct(
        \TemplateMonster\AjaxCatalog\Helper\Catalog\View\ContentAjaxResponse $helper)
    {
        $this->_helper = $helper;
    }

    public function afterGetSwatchData(\Magento\Swatches\Block\LayeredNavigation\RenderLayered $subject,$result){

        $multiAttrArr = $this->_helper->getMultiFilterAttributes();
        if(!$multiAttrArr || !is_array($multiAttrArr)) {
            return $result;
        }

        $request = $subject->getRequest();

        $attributeCode = $result['attribute_code'];
        if(in_array($attributeCode,$multiAttrArr)) {

            $attributeVal = $request->getParam($attributeCode);
            $attributeVal = (array)$attributeVal;

            $options = $result['options'];
            foreach($attributeVal as $item) {
                unset($options[$item]);
            }
            $result['options'] = $options;
        }

        return $result;
    }

}