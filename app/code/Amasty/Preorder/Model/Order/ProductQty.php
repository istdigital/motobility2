<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */


namespace Amasty\Preorder\Model\Order;

use Magento\CatalogInventory\Observer\ProductQty as NativeProductQty;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Quote\Model\Quote;

class ProductQty
{
    /**
     * @var NativeProductQty
     */
    private $productQty;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var array|null
     */
    private $placedItems = null;

    /**
     * @var Quote|null
     */
    private $quote = null;

    /**
     * @var array
     */
    private $backordered = [];

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    public function __construct(
        ModuleManager $moduleManager,
        NativeProductQty $productQty,
        CheckoutSession $checkoutSession
    ) {
        $this->productQty = $productQty;
        $this->checkoutSession = $checkoutSession;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param int $productId
     *
     * @return int
     */
    public function getPlacedQty($productId)
    {
        if ($this->placedItems === null && $this->isInventoryProcessed()) {
            $this->placedItems = $this->productQty->getProductQty(
                $this->getQuote()->getAllItems()
            );
        }
        $placedQty = isset($this->placedItems[$productId])
            ? $this->placedItems[$productId]
            : 0;

        return $placedQty - $this->getBackorderedQty($productId);
    }

    /**
     * Check if qty already substract from database.
     * If module Magento_InventorySales enabled than
     * plugin \Magento\InventorySales\Plugin\CatalogInventory\StockManagement\ProcessRegisterProductsSalePlugin ,
     * disable inventory processed from class \Magento\CatalogInventory\Model\StockManagement
     * @return bool
     */
    private function isInventoryProcessed()
    {
        return $this->getQuote()->getInventoryProcessed()
            && !$this->moduleManager->isEnabled('Magento_InventorySales');
    }

    /**
     * @return Quote|null
     */
    private function getQuote()
    {
        if ($this->quote === null) {
            $this->quote = $this->checkoutSession->getQuote();
        }

        return $this->quote;
    }

    /**
     * @param int $productId
     * @param $backorderedQty
     */
    public function addBackorderedQty($productId, $backorderedQty)
    {
        $this->backordered[$productId] = (int)$backorderedQty;
    }

    /**
     * @param int $productId
     *
     * @return int
     */
    public function getBackorderedQty($productId)
    {
        return isset($this->backordered[$productId])
            ? $this->backordered[$productId]
            : 0;
    }
}
