<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */


namespace Manadev\ProductCollection\Contracts;


use Manadev\ProductCollection\Query;

interface ProductCollection
{
    /**
     * @return Query
     */
    public function getQuery();

    public function load($printQuery = false, $logQuery = false);

    public function isLoaded();

    public function loadFacets();

    public function addCountToCategoriesOnSelect($select, $categoryCollection);
}