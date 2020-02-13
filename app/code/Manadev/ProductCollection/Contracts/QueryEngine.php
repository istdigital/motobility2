<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */


namespace Manadev\ProductCollection\Contracts;

interface QueryEngine
{
    /**
     * @return SupportedFilters
     */
    public function getSupportedFilters();

    public function run(ProductCollection $productCollection);

    public function isEnabled();
}