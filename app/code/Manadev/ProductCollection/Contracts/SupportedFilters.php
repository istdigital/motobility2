<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\ProductCollection\Contracts;

interface SupportedFilters
{
    /**
     * @return SupportedFilter[]
     */
    public function getList();

    /**
     * @param $name
     * @return SupportedFilter
     */
    public function get($name);
}