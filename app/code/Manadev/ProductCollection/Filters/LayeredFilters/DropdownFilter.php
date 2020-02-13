<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\ProductCollection\Filters\LayeredFilters;

use Manadev\ProductCollection\Enums\Operation;
use Manadev\ProductCollection\Contracts\Filter;

class DropdownFilter extends Filter
{
    /**
     * @var
     */
    protected $attributeId;
    /**
     * @var
     */
    protected $optionIds;
    /**
     * @var string
     */
    protected $operation;

    public function __construct($name, $attributeId, $optionIds, $operation = Operation::LOGICAL_OR) {
        parent::__construct($name);
        $this->attributeId = $attributeId;
        $this->optionIds = $optionIds;
        $this->operation = $operation;
    }

    public function getType() {
        return 'layered_dropdown';
    }

    public function getAttributeId() {
        return $this->attributeId;
    }

    public function getOptionIds() {
        return $this->optionIds;
    }

    public function getOperation() {
        return $this->operation;
    }
}