<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\ProductCollection\Filters\LayeredFilters;

use Manadev\ProductCollection\Contracts\Filter;
use Manadev\ProductCollection\Enums\Operation;

class CategoryFilter extends Filter
{
    /**
     * @var int[]
     */
    protected $ids;
    /**
     * @var string
     */
    protected $operation;

    public function __construct($name, $ids, $operation = Operation::LOGICAL_OR) {
        parent::__construct($name);
        $this->ids = $ids;
        $this->operation = $operation;
    }

    public function getType() {
        return 'layered_category';
    }

    public function getIds() {
        return $this->ids;
    }

    public function getOperation() {
        return $this->operation;
    }
}