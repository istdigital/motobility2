<?php
/** 
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation\Registries\FilterIndexers;

use Manadev\Core\Exceptions\InterfaceNotImplemented;
use Manadev\LayeredNavigation\Contracts\FilterIndexer;

abstract class BaseFilterIndexers {
    /**
     * @var FilterIndexer[]
     */
    protected $indexers;

    public function __construct(array $indexers)
    {
        foreach ($indexers as $indexer) {
            if (!($indexer instanceof FilterIndexer)) {
                throw new InterfaceNotImplemented(sprintf("'%s' does not implement '%s' interface.",
                    get_class($indexer), FilterIndexer::class));
            }
        }
        $this->indexers = $indexers;
    }

    public function getList() {
        return $this->indexers;
    }
}