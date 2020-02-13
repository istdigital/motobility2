<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\ProductCollection\Resources\Facets\Price;

use Magento\Framework\DB\Select;
use Manadev\ProductCollection\Contracts\Facet;
use Manadev\ProductCollection\Contracts\FacetResource;
use Manadev\ProductCollection\Facets\Price\ManualRangeFacet;
use Zend_Db_Expr;

class ManualRangeFacetResource extends BaseFacetResource
{
    /**
     * @param Select $select
     * @param Facet $facet
     * @return mixed
     */
    public function count(Select $select, Facet $facet) {
        /* @var $facet ManualRangeFacet */
        $db = $this->getConnection();

        // take category range. if not set take range from global config. If get more than max number of ranges add
        // all excessive counts to last range
        if (!$range = $facet->getQuery()->getCategory()->getData('filter_price_range')) {
            $range = $this->configuration->getDefaultPriceNavigationStep();
        }

        $counts = $db->fetchAll($this->countSelect($select, $range));

        if (!count($counts)) {
            return false;
        }

        $count = count($counts);

        $maxNumberOfIntervals = $this->configuration->getMaxNumberOfPriceIntervals();
        if ($count > $maxNumberOfIntervals) {
            for ($index = $maxNumberOfIntervals; $index < $count; $index++) {
                $counts[$maxNumberOfIntervals - 1]['count'] += $counts[$index]['count'];
            }

            $counts = array_slice($counts, 0, $maxNumberOfIntervals);
        }

        $this->helperResource->addAppliedRanges($counts, $range, $facet->getAppliedRanges());
        $count = count($counts);

        $minimumOptionCount = $facet->getHideWithSingleVisibleItem() ? 2 : 1;
        if (count($counts) < $minimumOptionCount) {
            return false;
        }

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
}