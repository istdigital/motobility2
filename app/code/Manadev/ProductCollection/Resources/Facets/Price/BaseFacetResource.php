<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\ProductCollection\Resources\Facets\Price;

use Magento\Framework\DB\Select;
use Manadev\ProductCollection\Contracts\Facet;
use Manadev\ProductCollection\Contracts\FacetResource;
use Zend_Db_Expr;

abstract class BaseFacetResource extends FacetResource
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct() {
        $this->_setMainTable('catalog_product_index_price');
    }

    protected function countSelect(Select $select, $range) {
        $this->helperResource->clearFacetSelect($select);

        $columns = [
            'range' => new Zend_Db_Expr("FLOOR(({$this->helperResource->getPriceExpression()}) / {$range})"),
            'count' => new Zend_Db_Expr('COUNT(*)'),
        ];

        $select->columns($columns)->group($columns['range'])->order($columns['range']);

        return $select;
    }

    protected function statSelect(Select $select) {
        $this->helperResource->clearFacetSelect($select);

        $select->columns([
            'min' => new Zend_Db_Expr("MIN({$this->helperResource->getPriceExpression()})"),
            'max' => new Zend_Db_Expr("MAX({$this->helperResource->getPriceExpression()})"),
            'count' => new Zend_Db_Expr("COUNT(*)"),
            'standard_deviation' => new Zend_Db_Expr("STDDEV_SAMP({$this->helperResource->getPriceExpression()})"),
        ]);

        return $select;
    }

    protected function implodeRanges($ranges) {
        if ($ranges === false) {
            return false;
        }

        return array_map(function($range) { return implode('-', $range); }, $ranges);
    }

    public function getFilterCallback(Facet $facet) {
        return $this->helperResource->dontApplyFilterNamed($facet->getName());
    }
}