<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\ProductCollection\Facets\Price;

use Manadev\ProductCollection\Contracts\Facet;

abstract class BaseFacet extends Facet
{
    /**
     * @var mixed
     */
    protected $appliedRanges;

    public function __construct($name, $appliedRanges) {
        parent::__construct($name);
        $this->appliedRanges = $appliedRanges ?: [];
    }

    /**
     * @return mixed
     */
    public function getAppliedRanges() {
        return $this->appliedRanges;
    }
}
