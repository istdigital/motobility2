<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */


namespace Amasty\Preorder\Plugin\Bundle\Model\ResourceModel\Selection;

use Magento\Bundle\Model\ResourceModel\Selection\Collection as SelectionCollection;
use Amasty\Preorder\Helper\Data as Helper;
use Zend_Db_Select;

class Collection
{
    const STOCK_PART = 'selection_can_change_qty';
    /**
     * Include verification for selection preorder
     *
     * @param SelectionCollection $subject
     * @param SelectionCollection $result
     * @return SelectionCollection
     */
    public function afterAddQuantityFilter(
        SelectionCollection $subject,
        SelectionCollection $result
    ) {
        $select = $subject->getSelect();
        if (!isset($select->getPart(\Zend_Db_Select::FROM)['stock_item'])) {
            $select->joinInner(
                ['stock_item' => $subject->getTable('cataloginventory_stock_item')],
                'selection.product_id = stock_item.product_id',
                []
            );
        }

        // set selection as in stock if on preorder
        $whereFilter = $select->getPart(Zend_Db_Select::WHERE);
        $qtyFilter = array_pop($whereFilter);
        if (strpos($qtyFilter, self::STOCK_PART) !== false) {
            $qtyFilter = preg_replace(
                '@(\sor\s)@mi',
                '$1' . 'stock_item.backorders = ' . Helper::BACKORDERS_PREORDER_OPTION . '$1',
                $qtyFilter,
                1
            );
        }
        $whereFilter[] = $qtyFilter;
        $select->setPart(Zend_Db_Select::WHERE, $whereFilter);

        return $subject;
    }
}
