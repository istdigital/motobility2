<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\ProductCollection\Facets\Price;

use Magento\Framework\DB\Select;

class EqualizedCountFacet extends BaseFacet
{
    protected $ranges;

    /**
     * @var Select
     */
    protected $preparationSelect;
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
         return 'price_equalized_count';
    }

    /**
     * @return mixed
     */
    public function getRanges() {
        return $this->ranges;
    }

    /**
     * @param mixed $ranges
     */
    public function setRanges($ranges) {
        $this->ranges = $ranges;
    }

    /**
     * @return Select
     */
    public function getPreparationSelect() {
        return $this->preparationSelect;
    }

    /**
     * @param Select $preparationSelect
     */
    public function setPreparationSelect($preparationSelect) {
        $this->preparationSelect = $preparationSelect;
    }


}