<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
namespace TemplateMonster\FeaturedProduct\Api;

interface FeaturedProductInterface
{
    public function getFeature(\Magento\Catalog\Model\ResourceModel\Product\Collection $collection,$collectionSize);

    public function getPreparedCollection($collectionSize,$productIds);
}