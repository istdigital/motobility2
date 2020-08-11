<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */


namespace Amasty\Preorder\Plugin\InventorySales\Model\ResourceModel\IsStockItemSalableCondition;

use Amasty\Preorder\Helper\Data;
use Magento\InventorySales\Model\ResourceModel\IsStockItemSalableCondition\BackordersCondition as NativeBackordersCondition;

class BackordersCondition
{
    /**
     * @var Data
     */
    private $helper;

    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param NativeBackordersCondition $backordersCondition
     * @param $condition
     *
     * @return string
     */
    public function afterExecute(NativeBackordersCondition $backordersCondition, $condition)
    {
        if ($this->helper->preordersEnabled()) {
            $condition = '(' . $condition . ') OR (legacy_stock_item.backorders = ' . Data::BACKORDERS_PREORDER_OPTION;
            if (!$this->helper->allowEmpty()) {
                $condition .= ' AND SUM(IF(source_item.status = 0, 0, quantity)) > 0';
            } elseif ($this->helper->disableForPositiveQty()) {
                $condition .= ' AND SUM(IF(source_item.status = 0, 0, quantity)) <= 0';
            }
            $condition .= ')';
        }

        return $condition;
    }
}
