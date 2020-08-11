<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */


declare(strict_types=1);

namespace Amasty\Preorder\Plugin\Catalog\Model\Product\Type;

use Amasty\Preorder\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\CatalogInventory\Model\StockRegistry;

class AbstractTypePlugin
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var StockRegistry
     */
    private $stockRegistry;

    public function __construct(
        Data $helper,
        StockRegistry $stockRegistry
    ) {
        $this->helper = $helper;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * @param AbstractType $subject
     * @param bool $salable
     * @param Product $product
     * @return bool
     */
    public function afterIsSalable(AbstractType $subject, bool $salable, Product $product): bool
    {
        if ($this->isProductShouldBeOutOfStock($product)) {
            $salable = false;
        }

        return $salable;
    }

    /**
     * If product has  Backorders set to Amasty BACKORDERS_PREORDER_OPTION , but dont satisfy qty condition ,
     * that mean product should be out of stock
     * @param Product $product
     * @return bool
     */
    protected function isProductShouldBeOutOfStock(Product $product): bool
    {
        $stockItem = $this->stockRegistry->getStockItem($product->getId());

        return $stockItem->getBackorders() == Data::BACKORDERS_PREORDER_OPTION
            && !$this->helper->allowEmpty()
            && !$this->helper->getIsProductPreorder($product);
    }
}
