<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */


namespace Amasty\Preorder\Plugin\InventorySales\Model\IsProductSalableCondition;

use Amasty\Preorder\Helper\Data;
use Amasty\Preorder\Model\ResourceModel\Inventory;
use Magento\InventorySales\Model\IsProductSalableCondition\BackOrderCondition as OriginalCondition;

class BackOrderCondition
{
    /**
     * @var \Magento\CatalogInventory\Model\StockRegistry
     */
    private $stockRegistry;

    /**
     * @var Inventory
     */
    private $inventoryResolver;

    public function __construct(
        Inventory $inventoryResolver,
        \Magento\CatalogInventory\Model\StockRegistry $stockRegistry
    ) {
        $this->stockRegistry = $stockRegistry;
        $this->inventoryResolver = $inventoryResolver;
    }

    /**
     * @param OriginalCondition $subject
     * @param \Closure $closure
     * @param string $sku
     * @param int $stockId
     *
     * @return bool
     */
    public function aroundExecute(OriginalCondition $subject, \Closure $closure, $sku, $stockId)
    {
        $stockItem = $this->stockRegistry->getStockItemBySku($sku, $stockId);
        if ($stockItem->getBackorders() == Data::BACKORDERS_PREORDER_OPTION) {
            $result = $this->inventoryResolver->getIsInStock($sku, $stockId);
        } else {
            $result = $closure($sku, $stockId);
        }

        return $result;
    }
}
