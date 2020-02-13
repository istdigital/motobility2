<?php
/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FeaturedProduct\Model;


class BestsellersProducts extends \TemplateMonster\FeaturedProduct\Model\FeaturedProductAbstract
{

    public function getFeature(\Magento\Catalog\Model\ResourceModel\Product\Collection $collection,$collectionSize)
    {
        $connection = $collection->getConnection();
        $orderTableAliasName = $connection->quoteIdentifier('order');
        $orderJoinCondition = [
            $orderTableAliasName . '.entity_id = order_items.order_id',
            $connection->quoteInto("{$orderTableAliasName}.state <> ?", \Magento\Sales\Model\Order::STATE_CANCELED),
        ];

        $collection->setPageSize(
            $collectionSize
        )->setCurPage(
            1
        )->getSelect()
            ->from(
            ['order_items' => $collection->getTable('sales_order_item')],
            ['ordered_qty' => 'SUM(order_items.qty_ordered)','product_id']
        )->joinInner(
            ['order' => $collection->getTable('sales_order')],
            implode(' AND ', $orderJoinCondition),
            []
        )->where(
            'e.entity_id = order_items.product_id and parent_item_id IS NULL'
        )->group(
            'order_items.product_id'
        )->order(
                'ordered_qty ' . \Magento\Framework\DB\Select::SQL_DESC
        )->having(
            'SUM(order_items.qty_ordered) > ?',
            0
        );
        return $collection;
    }

}