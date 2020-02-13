<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\ProductCollection\Resources\Facets\Decimal;

use Magento\Framework\DB\Select;
use Manadev\ProductCollection\Contracts\Facet;
use Manadev\ProductCollection\Contracts\FacetResource;
use Manadev\ProductCollection\Facets\Decimal\EqualizedRangeFacet;
use Zend_Db_Expr;

class EqualizedRangeFacetResource extends FacetResource
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct() {
        $this->_setMainTable('catalog_product_index_eav_decimal');
    }

    protected function expr(Select $select, Facet $facet) {
        /* @var $facet EqualizedRangeFacet */
        return $this->helperResource->getEavExpr($select, $this->getMainTable(), $facet->getAttributeId());
    }

    protected function statSelect(Select $select, Facet $facet) {
        $this->helperResource->clearFacetSelect($select);

        $select->columns([
            'min' => new Zend_Db_Expr("MIN({$this->expr($select, $facet)})"),
            'max' => new Zend_Db_Expr("MAX({$this->expr($select, $facet)})"),
            'count' => new Zend_Db_Expr("COUNT(*)"),
            'standard_deviation' => new Zend_Db_Expr("STDDEV_SAMP({$this->expr($select, $facet)})"),
        ]);

        return $select;
    }

    protected function countSelect(Select $select, Facet $facet, $range) {
        $this->helperResource->clearFacetSelect($select);

        $columns = [
            'range' => new Zend_Db_Expr("FLOOR(({$this->expr($select, $facet)}) / {$range})"),
            'count' => new Zend_Db_Expr('COUNT(*)'),
        ];

        $select->columns($columns)->group($columns['range'])->order($columns['range']);

        return $select;
    }

    /**
     * @param Select $select
     * @param Facet $facet
     * @return mixed
     */
    public function count(Select $select, Facet $facet) {
        /* @var $facet EqualizedRangeFacet */
        $db = $this->getConnection();

        if (($range = $this->getAppliedRange($facet)) === false) {
            $stats = $db->fetchRow($this->statSelect($select, $facet));
            if (is_null($stats['max'])) {
                return false;
            }

            $index = 1;
            do {
                $range = pow(10, strlen(floor($stats['max'])) - $index);
                $counts = $db->fetchAll($this->countSelect($select, $facet, $range));
                $index++;
            } while ($range > 10 && count($counts) < 2);
        }
        else {
            $counts = $db->fetchAll($this->countSelect($select, $facet, $range));
        }

        if (!count($counts)) {
            return false;
        }

        $this->helperResource->addAppliedRanges($counts, $range, $facet->getAppliedRanges());
        $count = count($counts);

        foreach ($counts as $index => &$item) {
            $from = $range * $item['range'];
            $to = $range * ($item['range'] + 1);
            unset($item['range']);

            $this->helperResource->formatDecimalRangeFacet($item, $from, $to, $index == 0, $index == $count - 1);
            $item['sort_order'] = $index;
            if (!isset($item['is_selected'])) {
                $item['is_selected'] = false;
            }
        }

        return $counts;
    }

    protected function getAppliedRange(EqualizedRangeFacet $facet) {
        if (!count($facet->getAppliedRanges())) {
            return false;
        }

        foreach ($facet->getAppliedRanges() as $range) {
            list($from, $to) = $range;
            if ($from !== '' && $to !== '') {
                $facetRange = abs((float)$to - (float)$from);
                if ($facetRange > 0.001) {
                    return abs((float)$to - (float)$from);
                }
            }
        }

        list($from, $to) = $facet->getAppliedRanges()[0];
        if ($from !== '') {
            return pow(10, strlen(floor($from)) - 1);
        }
        if ($to !== '') {
            return pow(10, strlen(floor($to)) - 1);
        }

        return false;
    }

    public function getFilterCallback(Facet $facet) {
        return $this->helperResource->dontApplyFilterNamed($facet->getName());
    }
}