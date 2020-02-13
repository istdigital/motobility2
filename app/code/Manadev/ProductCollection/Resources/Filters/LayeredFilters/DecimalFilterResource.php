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
use Manadev\ProductCollection\Filters\LayeredFilters\DecimalFilter;

class DecimalFilterResource extends FilterResource
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct() {
        $this->_setMainTable('catalog_product_index_eav_decimal');
    }

    /**
     * @param Select $select
     * @param Filter $filter
     * @param $callback
     * @return false|string
     */
    public function apply(Select $select, Filter $filter, $callback) {
        /* @var $filter DecimalFilter */
        if ($filter->getOperation() != Operation::LOGICAL_OR) {
            throw new NotSupported();
        }

        $alias = $filter->getFullName();
        $connection = $this->getConnection();

        $valueExpr = "`$alias`.`value`";

        $db = $this->getConnection();

        $parentExpr = "";
        foreach ($filter->getRanges() as $range) {
            list($from, $to) = $range;
            $expr = "";

            if ($from !== '') {
                $expr = $db->quoteInto("$valueExpr >= ?", $from);
            }
            if ($to !== '') {
                if(!$filter->getIsToRangeInclusive()) {
                    $to -= 0.001;
                }

                if ($expr) {
                    $expr .= " AND ";
                }
                $expr .= $db->quoteInto("$valueExpr <= ?", $to);
            }

            if ($expr) {
                if ($parentExpr) {
                    $parentExpr .= " OR ";
                }
                $parentExpr .= "($expr)";
            }
        }

        $select->joinInner([$alias => $this->getMainTable()],
            "`{$alias}`.`entity_id` = `e`.`entity_id` AND " .
            $connection->quoteInto("`{$alias}`.`attribute_id` = ?", $filter->getAttributeId()) . " AND " .
            $connection->quoteInto("`{$alias}`.`store_id` = ?", $this->getStoreId()));

        $select->where($parentExpr);

        return false;

    }
}