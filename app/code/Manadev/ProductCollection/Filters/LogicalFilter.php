<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\ProductCollection\Filters;

use Manadev\ProductCollection\Enums\Operation;
use Manadev\ProductCollection\Contracts\Filter;
use Manadev\Core\Exceptions\InvalidState;

class LogicalFilter extends Filter
{
    /**
     * @var Filter[]
     */
    protected $operands = [];

    /**
     * @var string
     */
    protected $operator;

    public function __construct($name, $operator = Operation::LOGICAL_AND) {
        parent::__construct($name);
        $this->operator = $operator;
    }

    public function addOperand(Filter $filter) {
        if (isset($this->operands[$filter->getName()])) {
            throw new InvalidState(sprintf('Filter %s already exists in logical operation filter %s',
                $filter->getName(), $this->getName()));
        }
        $this->operands[$filter->getName()] = $filter;

        if ($this->getFullName()) {
            $filter->setFullName($this->getFullName() . '_' . $filter->getName());
        }
        else {
            $filter->setFullName($filter->getName());
        }
    }

    public function removeOperand($filterName) {
        if (isset($this->operands[$filterName])) {
            unset($this->operands[$filterName]);
        }
    }

    public function getType() {
        return 'logical';
    }

    public function getOperator() {
        return $this->operator;
    }

    public function getOperands() {
        return $this->operands;
    }

    public function getOperand($name) {
        return isset($this->operands[$name]) ? $this->operands[$name] : false;
    }
}