<?php

namespace Manadev\LayeredNavigation\Plugins;

use Manadev\ProductCollection\Contracts\ProductCollection;

class StockHelperPlugin
{
    /**
     * @param $helper
     * @param ProductCollection $collection
     */
    public function beforeAddIsInStockFilterToCollection($helper, $collection) {
        // force catalog-inventory module to INNER JOIN stock status and filtering only in stock products
        //$collection->setFlag('require_stock_items', true);
    }
}