<?php
/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FeaturedProduct\Model;


class SaleProducts extends \TemplateMonster\FeaturedProduct\Model\FeaturedProductAbstract
{

    public function getFeature(\Magento\Catalog\Model\ResourceModel\Product\Collection $collection,$collectionSize)
    {

        $collection->setPageSize(
            $collectionSize
        )->setCurPage(
            1
        )->getSelect()->where('price_index.final_price < price_index.price');

        return $collection;
    }

}