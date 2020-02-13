<?php
/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FeaturedProduct\Model;


class ManualProducts extends \TemplateMonster\FeaturedProduct\Model\FeaturedProductAbstract
{

    public function getFeature(\Magento\Catalog\Model\ResourceModel\Product\Collection $collection,$collectionSize)
    {
        $collection->addFieldToFilter('entity_id',0);
    }

}