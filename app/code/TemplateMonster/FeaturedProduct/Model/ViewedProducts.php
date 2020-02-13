<?php
/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FeaturedProduct\Model;


class ViewedProducts extends \TemplateMonster\FeaturedProduct\Model\FeaturedProductAbstract
{

    public function getFeature(\Magento\Catalog\Model\ResourceModel\Product\Collection $collection,$collectionSize)
    {

        $collection->setPageSize(
            $collectionSize
        )->setCurPage(
            1
        )->getSelect()
            ->from(
                ['report_viewed_product_index' => $collection->getTable('report_viewed_product_index')],
                ['views' => 'COUNT(report_viewed_product_index.product_id)']

            )->where('e.entity_id = report_viewed_product_index.product_id'
            )->group(
                'report_viewed_product_index.product_id'
            )->order(
                'views ' . \Magento\Framework\DB\Select::SQL_DESC
            )->having(
                'COUNT(report_viewed_product_index.product_id) > ?',
                0
            );
        return $collection;
    }

}