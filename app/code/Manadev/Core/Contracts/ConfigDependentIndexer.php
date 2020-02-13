<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core\Contracts;

interface ConfigDependentIndexer
{
    public function reindexAll();

    /**
     * @return string[]
     */
    public function getUsedStoreConfigPaths();
}