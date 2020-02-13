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
use Manadev\ProductCollection\Filters\LayeredFilters\CategoryFilter;

class CategoryFilterResource extends FilterResource
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
        /* @var $filter CategoryFilter */
        switch ($filter->getOperation()) {
            case Operation::LOGICAL_OR: return $this->applyLogicalOr($select, $filter);
            case Operation::LOGICAL_AND: throw new NotSupported();
            case Operation::LOGICAL_NOT: return $this->applyLogicalNot($select, $filter);
        }
    }

    /**
     * @param Select $select
     * @param CategoryFilter $filter
     * @return string|false
     */
    protected function applyLogicalOr(Select $select, CategoryFilter $filter) {
        $from = $select->getPart(Select::FROM);
        if (isset($from['cat_index'])) {
            $from['cat_index']['joinCondition'] = preg_replace(
                "/(.*)(`?)cat_index(`?).(`?)category_id(`?)=('?)(\\d+)('?)(.*)/",
                "$1$2cat_index$3.$4category_id$5 IN (" . implode(',', $filter->getIds()) . ")$9",
                $from['cat_index']['joinCondition']
            );
            $select->setPart(Select::FROM, $from);
        }

        return false;
    }

    /**
     * @param Select $select
     * @param CategoryFilter $filter
     * @return string|false
     */
    protected function applyLogicalNot(Select $select, CategoryFilter $filter) {
        throw new NotImplemented();
    }
}