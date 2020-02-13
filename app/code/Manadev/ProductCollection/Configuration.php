<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\ProductCollection;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Manadev\Core\Resources\CompatibilityResource;

class Configuration
{
    const QUERY_ENGINE = 'catalog/search/engine';
    const EQUALIZED_COUNT_INTERVAL_DIVISION_LIMIT = 'catalog/layered_navigation/interval_division_limit';
    const DEFAULT_PRICE_NAVIGATION_STEP = 'catalog/layered_navigation/price_range_step';
    const MAX_NUMBER_OF_PRICE_INTERVALS = 'catalog/layered_navigation/price_range_max_intervals';
    const PRICE_RANGE_CALCULATION_METHOD = 'catalog/layered_navigation/price_range_calculation';
    const PRODUCT_COLLECTION_QUERY_LOGGING = 'mana_core/log/product_collection_queries';
    const FACET_COUNTING_QUERY_LOGGING = 'mana_core/log/facet_counting_queries';
    const BATCH_FILTER_COUNTING = 'mana_core/experimental/batch_filter_counting';
    const CHECK_STOCK_STATUS = 'mana_core/experimental/check_stock_status';
    const SHOW_OUT_OF_STOCK_INVENTORY = 'cataloginventory/options/show_out_of_stock';
    const USE_PRODUCT_TEMP_TABLE = 'mana_core/experimental/use_product_temp_table';
    const USE_COUNT_TEMP_TABLE = 'mana_core/experimental/use_count_temp_table';
    const CALCULATE_TAX_IN_PRICE_FILTER = 'mana_core/experimental/price_filter_tax_calculation';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Flat\State
     */
    protected $categoryFlatState;
    /**
     * @var CompatibilityResource
     */
    protected $compatibilityResource;

    public function __construct(ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState, CompatibilityResource $compatibilityResource)
    {
        $this->scopeConfig = $scopeConfig;
        $this->categoryFlatState = $categoryFlatState;
        $this->compatibilityResource = $compatibilityResource;
    }

    public function getQueryEngine() {
        return $this->scopeConfig->getValue(static::QUERY_ENGINE);
    }

    public function areCategoriesFlat() {
        return $this->categoryFlatState->isAvailable();
    }

    public function getEqualizedCountIntervalDivisionLimit() {
        return $this->scopeConfig->getValue(static::EQUALIZED_COUNT_INTERVAL_DIVISION_LIMIT, ScopeInterface::SCOPE_STORE);
    }

    public function getDefaultPriceNavigationStep() {
        return $this->scopeConfig->getValue(static::DEFAULT_PRICE_NAVIGATION_STEP, ScopeInterface::SCOPE_STORE);
    }

    public function getMaxNumberOfPriceIntervals() {
        return $this->scopeConfig->getValue(static::MAX_NUMBER_OF_PRICE_INTERVALS, ScopeInterface::SCOPE_STORE);
    }

    public function getPriceRangeCalculationMethod() {
        return $this->scopeConfig->getValue(static::PRICE_RANGE_CALCULATION_METHOD, ScopeInterface::SCOPE_STORE);
    }

    public function isProductCollectionQueryLoggingEnabled() {
        return $this->scopeConfig->isSetFlag(static::PRODUCT_COLLECTION_QUERY_LOGGING);
    }

    public function isFacetCountingQueryLoggingEnabled() {
        return $this->scopeConfig->isSetFlag(static::FACET_COUNTING_QUERY_LOGGING);
    }

    public function isBatchFilterCountingEnabled() {
        //return $this->scopeConfig->isSetFlag(static::BATCH_FILTER_COUNTING);
        return true;
    }

    public function isCheckingStockStatusEnabled() {
        if (!$this->scopeConfig->isSetFlag(static::CHECK_STOCK_STATUS)) {
            return false;
        }

        if ($this->scopeConfig->isSetFlag(static::SHOW_OUT_OF_STOCK_INVENTORY)) {
            return false;
        }

        if (!$this->compatibilityResource->eavIndexSourceIdExists()) {
            return false;
        }

        return true;
    }

    public function useProductTempTable() {
        //return $this->scopeConfig->isSetFlag(static::USE_PRODUCT_TEMP_TABLE);
        return true;
    }

    public function useCountTempTable() {
        //return $this->scopeConfig->isSetFlag(static::USE_COUNT_TEMP_TABLE);
        return true;
    }

    public function calculateTaxInPriceFilter() {
        return $this->scopeConfig->isSetFlag(static::CALCULATE_TAX_IN_PRICE_FILTER);
    }
}