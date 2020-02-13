<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\ProductCollection\Resources\Facets\Price;

use Magento\Framework\DB\Select;
use Manadev\ProductCollection\Contracts\Facet;
use Manadev\ProductCollection\Contracts\FacetResource;
use Manadev\ProductCollection\Facets\Price\EqualizedRangeFacet;
use Zend_Db_Expr;

class EqualizedRangeFacetResource extends BaseFacetResource
{

    /**
     * @param Select $select
     * @param Facet $facet
     * @return mixed
     */
    public function count(Select $select, Facet $facet) {
        /* @var $facet EqualizedRangeFacet */
        $db = $this->getConnection();

        if (($range = $this->getAppliedRange($facet)) === false) {
            $stats = $db->fetchRow($this->statSelect($select));
            if (is_null($stats['max'])) {
                return false;
            }

            $index = 1;
            do {
                $range = pow(10, strlen(floor($stats['max'])) - $index);
                $counts = $db->fetchAll($this->countSelect($select, $range));
                $index++;
            } while ($range > 10 && count($counts) < 2);
        }
        else {
            $counts = $db->fetchAll($this->countSelect($select, $range));
        }

        $minimumOptionCount = $facet->getHideWithSingleVisibleItem() ? 2 : 1;
        if (count($counts) < $minimumOptionCount) {
            return false;
        }

        $this->helperResource->addAppliedRanges($counts, $range, $facet->getAppliedRanges());
        $count = count($counts);

        foreach ($counts as $index => &$item) {
            $from = $range * $item['range'];
            $to = $range * ($item['range'] + 1);
            unset($item['range']);

            $this->helperResource->formatPriceRangeFacet($item, $from, $to, $index == 0, $index == $count - 1);
            $item['sort_order'] = $index;
            if (!isset($item['is_selected'])) {
                $item['is_selected'] = false;
            }
        }

        return $counts;
    }

    protected function getAppliedRange(EqualizedRangeFacet $facet) {
        return false;
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
}