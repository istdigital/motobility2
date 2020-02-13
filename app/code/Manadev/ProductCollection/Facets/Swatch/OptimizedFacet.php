<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\ProductCollection\Facets\Swatch;

use Manadev\ProductCollection\Facets\Dropdown\OptimizedFacet as BaseOptimizedFacet;

class OptimizedFacet extends BaseOptimizedFacet
{
    public function getType() {
        return 'swatch_optimized';
    }
}
