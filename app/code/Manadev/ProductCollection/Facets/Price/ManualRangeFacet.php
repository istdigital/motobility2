<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\ProductCollection\Facets\Price;

class ManualRangeFacet extends BaseFacet
{
    /**
     * @var
     */
    protected $hideWithSingleVisibleItem;

    public function __construct($name, $appliedRanges, $hideWithSingleVisibleItem) {
        parent::__construct($name, $appliedRanges);
        $this->hideWithSingleVisibleItem = $hideWithSingleVisibleItem;
    }

    public function getHideWithSingleVisibleItem() {
        return $this->hideWithSingleVisibleItem;
    }

    public function getType() {
         return 'price_manual_range';
    }
}