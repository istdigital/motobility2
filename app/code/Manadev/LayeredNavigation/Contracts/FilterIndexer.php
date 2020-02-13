<?php
/** 
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */


namespace Manadev\LayeredNavigation\Contracts;


use Manadev\LayeredNavigation\Models\Filter;

interface FilterIndexer {
    /**
     * Returns array of store configuration paths which are used in `index`
     * method of this data source
     * @return string[]
     */
    public function getUsedStoreConfigPaths();

    /**
     * Inserts or updates records in `mana_filter` table on global level
     * @param array $changes
     * @return \Magento\Framework\DB\Select
     */
    public function index($changes = ['all']);
}