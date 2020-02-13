<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\ProductCollection\Resources\Filters\LayeredFilters;

use Magento\Framework\DB\Select;
use Manadev\Core\Exceptions\NotImplemented;
use Manadev\ProductCollection\Contracts\Filter;
use Manadev\ProductCollection\Contracts\FilterResource;
use Manadev\ProductCollection\Enums\Operation;
use Manadev\ProductCollection\Filters\LayeredFilters\DropdownFilter;

class DropdownFilterResource extends FilterResource
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct() {
        $this->_setMainTable('catalog_product_index_eav');
    }

    /**
     * @param Select $select
     * @param Filter $filter
     * @param $callback
     * @return false|string
     */
    public function apply(Select $select, Filter $filter, $callback) {
        /* @var $filter DropdownFilter */
        switch ($filter->getOperation()) {
            case Operation::LOGICAL_OR: return $this->applyLogicalOr($select, $filter);
            case Operation::LOGICAL_AND: return $this->applyLogicalAnd($select, $filter);
            case Operation::LOGICAL_NOT: return $this->applyLogicalNot($select, $filter);
        }
    }

    /**
     * @param Select $select
     * @param DropdownFilter $filter
     * @return string|false
     */
    protected function applyLogicalOr(Select $select, DropdownFilter $filter) {
        $alias = $filter->getFullName();
        $connection = $this->getConnection();

        $select->joinInner([$alias => $this->getMainTable()],
            "`{$alias}`.`entity_id` = `e`.`entity_id` AND " . 
            $connection->quoteInto("`{$alias}`.`attribute_id` = ?", $filter->getAttributeId()) . " AND " .
            $connection->quoteInto("`{$alias}`.`store_id` = ?", $this->getStoreId()) . " AND " .
            "`{$alias}`.`value` IN (" . implode(',', $filter->getOptionIds()) . ")" , null);

        $this->helperResource->checkStockStatus($select, $alias);

        return false;
    }

    /**
     * @param Select $select
     * @param DropdownFilter $filter
     * @return string|false
     */
    protected function applyLogicalAnd(Select $select, DropdownFilter $filter) {
        foreach ($filter->getOptionIds() as $optionId) {
            $alias = $filter->getFullName() . '__' . $optionId;
            $connection = $this->getConnection();

            $select->joinInner([$alias => $this->getMainTable()],
                "`{$alias}`.`entity_id` = `e`.`entity_id` AND " .
                $connection->quoteInto("`{$alias}`.`attribute_id` = ?", $filter->getAttributeId()) . " AND " .
                $connection->quoteInto("`{$alias}`.`store_id` = ?", $this->getStoreId()) . " AND " .
                $connection->quoteInto("`{$alias}`.`value`  = ?", $optionId), null);

            $this->helperResource->checkStockStatus($select, $alias);
        }

        return false;
    }

    /**
     * @param Select $select
     * @param DropdownFilter $filter
     * @return string|false
     */
    protected function applyLogicalNot(Select $select, DropdownFilter $filter) {
        throw new NotImplemented();
    }
}