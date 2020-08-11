<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */

/**
 * Copyright © 2016 Amasty. All rights reserved.
 */

namespace Amasty\Preorder\Plugin;

use Amasty\Preorder\Model\ResourceModel\OrderPreorder;
use Magento\Framework\DB\Select;

class UiSearchResult
{
    public function beforeLoad(\Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult $subject)
    {
        if (strpos($subject->getMainTable(), 'sales_order_grid') !== false) {
            $this->_injectSelect($subject);
            return;
        }
    }

    public function beforeGetSelectCountSql(
        \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult $subject
    ) {
        if (strpos($subject->getMainTable(), 'sales_order_grid') !== false) {
            $this->_injectSelect($subject);
        }
    }

    public function beforeGetAllIds(\Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult $subject)
    {
        if (strpos($subject->getMainTable(), 'sales_order_grid') !== false) {
            $this->_injectSelect($subject);
        }
    }

    protected function _injectSelect(\Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult $subject)
    {
        $select = $subject->getSelect();
        if (strpos((string)$select, OrderPreorder::MAIN_TABLE) === false) {
            $select->joinLeft(
                ['preorder' => $subject->getTable(OrderPreorder::MAIN_TABLE)],
                'preorder.order_id=main_table.entity_id',
                ['is_preorder' => new \Zend_Db_Expr("IF(preorder.is_preorder IS NULL, 0, preorder.is_preorder)")]
            );
            //echo $select;
        }
        $where = $select->getPart(Select::WHERE);
        foreach ($where as &$part) {
            if (strpos($part, '`is_preorder` = \'0\'') !== false) {
                $part = str_replace("= '0'", 'IS NULL', $part);
            }
        }
        $select->setPart(Select::WHERE, $where);
        $select;
    }
}
