<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\ProductCollection\Resources;

use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;
use Manadev\ProductCollection\Configuration;
use Manadev\ProductCollection\Contracts\Filter;
use Magento\CatalogInventory\Model\Stock;
use Manadev\ProductCollection\Enums\Operation;
use Manadev\ProductCollection\Filters\LayeredFilters\DropdownFilter;

class HelperResource extends Db\AbstractDb
{
    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;
    /**
     * @var TaxResource
     */
    protected $taxResource;
    protected $priceExpr;
    protected $currencyRate;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var Configuration
     */
    protected $configuration;

    public function __construct(Db\Context $context, PriceCurrencyInterface $priceCurrency,
        TaxResource $taxResource, StoreManagerInterface $storeManager, Configuration $configuration,
        $resourcePrefix = null)
    {
        parent::__construct($context, $resourcePrefix);
        $this->priceCurrency = $priceCurrency;
        $this->taxResource = $taxResource;
        $this->storeManager = $storeManager;
        $this->configuration = $configuration;
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct() {
        $this->_setMainTable('catalog_product_entity');
    }

    public function getPriceExpression() {
        if (!$this->priceExpr) {
            $this->priceExpr = "`price_index`.`min_price`";
            $this->priceExpr = $this->taxResource->applyTaxToPriceExpression($this->priceExpr);
            $this->priceExpr = $this->applyCurrencyRateToPriceExpression($this->priceExpr);
            $this->priceExpr = "ROUND($this->priceExpr, 2)";
        }

        return $this->priceExpr;
    }

    public function formatPriceRangeFacet(&$item, $from, $to, $isFirst, $isLast) {
        if ($isFirst) {
            $item['label'] = __("Below %1", $this->priceCurrency->format($to));
            if (!isset($item['value'])) {
                $item['value'] = "-{$to}";
            }
            $item['from'] = null;
            $item['to'] = $to;
        }
        elseif ($isLast) {
            $item['label'] = __("%1 and above", $this->priceCurrency->format($from));
            if (!isset($item['value'])) {
                $item['value'] = "{$from}-";
            }
            $item['from'] = $from;
            $item['to'] = null;
        }
        else {
            $item['label'] = __("%1 - %2", $this->priceCurrency->format($from), $this->priceCurrency->format($to));
            if (!isset($item['value'])) {
                $item['value'] = "{$from}-{$to}";
            }
            $item['from'] = $from;
            $item['to'] = $to;
        }
    }

    public function formatDecimal($value) {
        return sprintf("%d", round($value));
    }

    public function formatDecimalRangeFacet(&$item, $from, $to, $isFirst, $isLast) {
        if ($isFirst) {
            $item['label'] = __("Below %1", $this->formatDecimal($to));
            if (!isset($item['value'])) {
                $item['value'] = "-{$to}";
            }
            $item['from'] = null;
            $item['to'] = $to;
        }
        elseif ($isLast) {
            $item['label'] = __("%1 and above", $this->formatDecimal($from));
            if (!isset($item['value'])) {
                $item['value'] = "{$from}-";
            }
            $item['from'] = $from;
            $item['to'] = null;
        }
        else {
            $item['label'] = __("%1 - %2", $this->formatDecimal($from), $this->formatDecimal($to));
            if (!isset($item['value'])) {
                $item['value'] = "{$from}-{$to}";
            }
            $item['from'] = $from;
            $item['to'] = $to;
        }
    }

    public function formatCustomRangeFacet(&$item, $from, $to, $format, $showThousandSeparator) {
        if($showThousandSeparator) {
            $from = number_format($from);
            $to = number_format($to);
        }

        if ($format == '$0') {
            $this->formatPriceRangeFacet($item, $from, $to, false, false);
            return;
        }

        $item['label'] = __("%1 - %2", str_replace("0", $from, $format), str_replace("0", $to, $format));
        if (!isset($item['value'])) {
            $item['value'] = "{$from}-{$to}";
        }
    }

    public function formatDropdownRangeFacet(&$item, $from, $to) {
        $item['label'] = __("%1 - %2", $from, $to);
        if (!isset($item['value'])) {
            $item['value'] = "{$from}-{$to}";
        }
    }

    public function clearFacetSelect(Select $select) {
        $select->reset(Select::COLUMNS);
        $select->reset(Select::ORDER);
        $select->reset(Select::GROUP);
        $select->reset(Select::LIMIT_COUNT);
        $select->reset(Select::LIMIT_OFFSET);
    }

    public function addAppliedRanges(&$counts, $range, $appliedRanges) {
        foreach ($appliedRanges as $appliedRange) {
            list($from, $to) = $appliedRange;
            $index = $from === '' ? floor($to / $range) - 1 : floor($from / $range);

            $found = false;

            foreach ($counts as &$item) {
                if ($item['range'] == $index) {
                    $found = true;
                    $item['is_selected'] = true;
                    $item['value'] = "{$from}-{$to}";
                    break;
                }
            }

            if (!$found) {
                $counts[] = ['range' => $index, 'count' => 0, 'is_selected' => true];
            }
        }
        usort($counts, function($a, $b) {
            if ((int)$a['range'] < (int)$b['range']) return -1;
            if ((int)$a['range'] > (int)$b['range']) return 1;
            return 0;
        });
    }

    protected function applyCurrencyRateToPriceExpression($priceExpr) {
        if ($this->getCurrencyRate() == 1) {
            return $priceExpr;
        }

        return "($priceExpr)*" . round($this->getCurrencyRate(), 4);
    }

    public function getCurrencyRate()
    {
        if ($this->currencyRate === null) {
            $this->currencyRate = $this->storeManager->getStore()->getCurrentCurrencyRate();
            if (!$this->currencyRate) {
                $this->currencyRate = 1;
            }
        }

        return $this->currencyRate;
    }

    public function dontApplyFilterNamed($name) {
        return function (Filter $filter) use ($name) {
            return $filter->getFullName() != 'layered_nav_' . $name;
        };
    }

    public function dontApplyLogicalOrFilterNamed($name) {
        return function (Filter $filter) use ($name) {
            if ($filter->getFullName() != 'layered_nav_' . $name) {
                // apply other filters than this filter
                return true;
            }

            if ($filter->getType() != 'layered_dropdown') {
                return true;
            }

            /* @var DropdownFilter $filter */
            if ($filter->getOperation() != Operation::LOGICAL_OR) {
                return true;
            }

            return false;
        };
    }

    public function dontApplyLayeredNavigationFilters() {
        return function (Filter $filter) {
            return strpos($filter->getFullName(), 'layered_nav_') !== 0;
        };
    }

    public function getEavExpr(Select $select, $tableName, $attributeId){
        $storeId = $this->storeManager->getStore()->getId();
        $db = $this->getConnection();

        $from = $select->getPart(Select::FROM);

        if (!isset($from['eav'])) {
            $select->joinInner(array('eav' => $tableName),
                "`eav`.`entity_id` = `e`.`entity_id` AND
                {$db->quoteInto("`eav`.`attribute_id` = ?", $attributeId)} AND
                {$db->quoteInto("`eav`.`store_id` = ?", $storeId)}", null);
        }

        return "`eav`.`value`";
    }

    /**
     * @param Select $select
     * @param string $alias
     */
    public function checkStockStatus($select, $alias) {
        if (!$this->configuration->isCheckingStockStatusEnabled()) {
            return;
        }

        $stock = "{$alias}__stock";
        $websiteId = 0;
        $stockId = Stock::DEFAULT_STOCK_ID;
        $select->joinInner([$stock => $this->getTable('cataloginventory_stock_status')],
            "`{$alias}`.`source_id` = `{$stock}`.`product_id` AND " .
            "`{$stock}`.`website_id` = $websiteId AND " .
            "`{$stock}`.`stock_id` = {$stockId} AND ".
            "`{$stock}`.`stock_status` = 1", null);
    }
}