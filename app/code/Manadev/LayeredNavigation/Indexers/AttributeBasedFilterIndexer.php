<?php
/** 
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation\Indexers;

use Magento\Framework\Mview\ActionInterface as MviewInterface;
use Magento\Framework\Indexer\ActionInterface as IndexerInterface;
use Manadev\LayeredNavigation\Resources\Indexers\FilterIndexer;

class AttributeBasedFilterIndexer implements IndexerInterface, MviewInterface {
    /**
     * @var FilterIndexer
     */
    protected $resource;

    public function __construct(FilterIndexer $resource) {
        $this->resource = $resource;
    }

    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull() {
        $this->resource->reindexAll();
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     */
    public function executeList(array $ids) {
        $this->resource->reindexChangedAttributes($ids);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     */
    public function executeRow($id) {
        $this->resource->reindexChangedAttributes([$id]);
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @return void
     */
    public function execute($ids) {
        $this->resource->reindexChangedAttributes($ids);
    }
}