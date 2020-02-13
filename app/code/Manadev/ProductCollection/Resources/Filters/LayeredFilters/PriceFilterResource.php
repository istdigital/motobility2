<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\ProductCollection\Resources\Filters\LayeredFilters;

use Magento\Framework\DB\Select;
use Manadev\Core\Exceptions\NotImplemented;
use Manadev\Core\Exceptions\NotSupported;
use Manadev\ProductCollection\Contracts\Filter;
use Manadev\ProductCollection\Contracts\FilterResource;
use Manadev\ProductCollection\Enums\Operation;
use Manadev\ProductCollection\Filters\LayeredFilters\PriceFilter;

class PriceFilterResource extends FilterResource
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct() {
        $this->_setMainTable('catalog_product_entity');
    }

    /**
     * @param Select $select
     * @param Filter $filter
     * @param $callback
     * @return false|string
     */
    public function apply(Select $select, Filter $filter, $callback) {
        /* @var $filter PriceFilter */
        switch ($filter->getOperation()) {
            case Operation::LOGICAL_OR: return $this->applyLogicalOr($select, $filter);
            case Operation::LOGICAL_AND: throw new NotSupported();
            case Operation::LOGICAL_NOT: return $this->applyLogicalNot($select, $filter);
        }
    }

    protected function applyLogicalOr(Select $select, PriceFilter $filter) {
        $priceExpr = $this->helperResource->getPriceExpression();

        $db = $this->getConnection();

        $parentExpr = "";
        foreach ($filter->getRanges() as $range) {
            list($from, $to) = $range;
            $expr = "";

            if ($from !== '') {
                $expr = $db->quoteInto("$priceExpr >= ?", $from);
            }
            if ($to !== '') {
                if (!$filter->getInclusive()) {
                    $to -= 0.001;
                }
                if ($expr) {
                    $expr .= " AND ";
                }
                $expr .= $db->quoteInto("$priceExpr <= ?", $to);
            }

            if ($expr) {
                if ($parentExpr) {
                    $parentExpr .= " OR ";
                }
                $parentExpr .= "($expr)";
            }
        }
        $select->where($parentExpr);
    }

    protected function applyLogicalNot(Select $select, PriceFilter $filter) {
        throw new NotImplemented();
    }
}