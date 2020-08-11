<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */


namespace Amasty\Preorder\Plugin\InventoryCatalog\Plugin\CatalogInventory\Model\Spi\StockStateProvider;

use Amasty\Preorder\Helper\Data;
use Magento\CatalogInventory\Api\Data\StockItemInterface;

class AdaptVerifyStockToNegativeMinQtyPlugin
{
    /**
     * @var StockItemInterface
     */
    private $stockItem;

    /**
     * @param $subject
     * @param $origSubject
     * @param $result
     * @param $stockItem
     *
     * @return array
     */
    public function beforeAfterVerifyStock($subject, $origSubject, $result, $stockItem)
    {
        $this->stockItem = $stockItem;

        return [$origSubject, $result, $stockItem];
    }

    /**
     * @param $subject
     * @param $result
     *
     * @return bool
     */
    public function afterAfterVerifyStock($subject, $result)
    {
        if ($this->stockItem->getBackorders() == Data::BACKORDERS_PREORDER_OPTION) {
            $result = true;
        }

        return $result;
    }
}
