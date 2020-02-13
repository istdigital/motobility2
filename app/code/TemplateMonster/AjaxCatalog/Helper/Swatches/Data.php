<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\AjaxCatalog\Helper\Swatches;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;

class Data extends \Magento\Swatches\Helper\Data
{

    /**
     * @param ProductCollection $productCollection
     * @param array $attributes
     * @return void
     */
    protected function addFilterByAttributes(ProductCollection $productCollection, array $attributes)
    {

        foreach ($attributes as $code => $option) {
            $option = is_array($option) ? $option : [$option];
            foreach($option as $val) {
                $productCollection->addAttributeToFilter($code, ['in' => $val]);
            }
        }
    }

}