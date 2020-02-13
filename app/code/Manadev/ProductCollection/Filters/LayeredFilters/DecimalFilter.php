<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\ProductCollection\Filters\LayeredFilters;

use Manadev\ProductCollection\Enums\Operation;
use Manadev\ProductCollection\Contracts\Filter;

class DecimalFilter extends Filter
{
    /**
     * @var
     */
    protected $attributeId;
    /**
     * @var
     */
    protected $ranges;
    /**
     * @var string
     */
    protected $operation;
    /**
     * @var bool
     */
    protected $isToRangeInclusive;

    public function __construct($name, $attributeId, $ranges, $isToRangeInclusive = false, $operation = Operation::LOGICAL_OR) {
        parent::__construct($name);
        $this->attributeId = $attributeId;
        $this->ranges = $ranges;
        $this->operation = $operation;
        $this->isToRangeInclusive = $isToRangeInclusive;
    }

    public function getType() {
        return 'layered_decimal';
    }

    public function getAttributeId() {
        return $this->attributeId;
    }

    public function getRanges() {
        return $this->ranges;
    }

    public function getOperation() {
        return $this->operation;
    }

    public function getIsToRangeInclusive() {
        return $this->isToRangeInclusive;
    }
}