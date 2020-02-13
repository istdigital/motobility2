<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\AjaxCatalog\Block\LayeredNavigation;

class RenderLayered extends \Magento\Swatches\Block\LayeredNavigation\RenderLayered
{

    /**
     * @param FilterItem[] $filterItems
     * @param integer $id
     * @return bool|FilterItem
     */
    protected function getFilterItemById(array $filterItems, $id)
    {

        foreach ($filterItems as $item) {
            if(is_array($item->getValue())) {
                if (in_array($id,$item->getValue())) {
                    return $item;
                }
            } else {
                if ($item->getValue() == $id) {
                    return $item;
                }
            }
        }
        return false;
    }

}