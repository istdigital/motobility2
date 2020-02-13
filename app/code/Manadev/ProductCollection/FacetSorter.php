<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\ProductCollection;

use Manadev\ProductCollection\Facets\Dropdown\BaseFacet;

class FacetSorter
{
    /**
     * @param BaseFacet $facet
     * @param array $data
     */
    public function sort($facet, &$data) {
        $this->{'sort_by_' . $facet->getSortBy() . ((int)$facet->isShowSelectedOptionsFirst() ? '_selected' : '')}($data);
    }

    protected function sort_by_position(&$data) {
        usort($data, function($a, $b) {
            return $this->comparePositions($a, $b);
        });
    }

    protected function sort_by_name_alphabetic(&$data) {
        usort($data, function($a, $b) {
            return $this->compareAlphabeticNames($a, $b);
        });
    }

    protected function sort_by_name_numeric(&$data) {
        usort($data, function($a, $b) {
            return $this->compareNumericNames($a, $b);
        });
    }

    protected function sort_by_count(&$data) {
        usort($data, function($a, $b) {
            return $this->compareCounts($a, $b);
        });
    }

    protected function sort_by_position_selected(&$data) {
        usort($data, function($a, $b) {
            if (($result = $this->compareSelected($a, $b)) != 0) {
                return $result;
            }
            return $this->comparePositions($a, $b);
        });
    }

    protected function sort_by_name_alphabetic_selected(&$data) {
        usort($data, function($a, $b) {
            if (($result = $this->compareSelected($a, $b)) != 0) {
                return $result;
            }
            return $this->compareAlphabeticNames($a, $b);
        });
    }

    protected function sort_by_name_numeric_selected(&$data) {
        usort($data, function($a, $b) {
            if (($result = $this->compareSelected($a, $b)) != 0) {
                return $result;
            }
            return $this->compareNumericNames($a, $b);
        });
    }

    protected function sort_by_count_selected(&$data) {
        usort($data, function($a, $b) {
            if (($result = $this->compareSelected($a, $b)) != 0) {
                return $result;
            }
            return $this->compareCounts($a, $b);
        });
    }

    protected function comparePositions($a, $b) {
        if ((int)$a['sort_order'] < (int)$b['sort_order']) return -1;
        if ((int)$a['sort_order'] > (int)$b['sort_order']) return 1;

        return 0;
    }

    protected function compareAlphabeticNames($a, $b) {
        if ($a['label'] < $b['label']) return -1;
        if ($a['label'] > $b['label']) return 1;

        return 0;
    }

    protected function compareNumericNames($a, $b) {
        if ((float)$a['label'] < (float)$b['label']) return -1;
        if ((float)$a['label'] > (float)$b['label']) return 1;

        return 0;
    }

    protected function compareCounts($a, $b) {
        if ((int)$a['count'] > (int)$b['count']) return -1;
        if ((int)$a['count'] < (int)$b['count']) return 1;

        return 0;
    }

    protected function compareSelected($a, $b) {
        if ((int)$a['is_selected'] > (int)$b['is_selected']) return -1;
        if ((int)$a['is_selected'] < (int)$b['is_selected']) return 1;

        return 0;
    }
}