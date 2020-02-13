<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\ProductCollection\Resources;

use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorage;
use Manadev\ProductCollection\Configuration;
use Manadev\ProductCollection\Contracts\Facet;
use Manadev\ProductCollection\Contracts\FacetBatch;
use Manadev\ProductCollection\Contracts\FacetResource;
use Manadev\ProductCollection\Contracts\Filter;
use Manadev\ProductCollection\Contracts\SupportedFilters;
use Manadev\ProductCollection\Contracts\QueryEngine;
use Manadev\ProductCollection\Factory;
use Manadev\ProductCollection\Query;
use Manadev\ProductCollection\Registries\FacetResources;
use Manadev\ProductCollection\Registries\FilterResources;
use Manadev\ProductCollection\Contracts\ProductCollection;
use Manadev\ProductCollection\Resources\Collections\FullTextProductCollection;

class QueryEngineResource implements QueryEngine
{
    /**
     * @var FilterResources
     */
    protected $filterResources;
    /**
     * @var FacetResources
     */
    protected $facetResources;
    /**
     * @var Configuration
     */
    protected $configuration;
    /**
     * @var Factory
     */
    protected $factory;

    protected $tempTables = [];

    public function __construct(FilterResources $filterResources, FacetResources $facetResources,
        Configuration $configuration, Factory $factory)
    {
        $this->filterResources = $filterResources;
        $this->facetResources = $facetResources;
        $this->configuration = $configuration;
        $this->factory = $factory;
    }

    /**
     * @return SupportedFilters
     */
    public function getSupportedFilters() {
        return $this->filterResources;
    }

    /**
     * @param ProductCollection $productCollection
     */
    public function run(ProductCollection $productCollection) {
        /* @var FullTextProductCollection $productCollection */
        $query = $productCollection->getQuery();

        /* @var FacetBatch[] $batches */
        $batches = [];

        foreach ($query->getFacets() as $facet) {
            $resource = $this->facetResources->get($facet->getType());

            if ($this->registerForBatchCounting($batches, $resource, $facet)) {
                continue;
            }

            if ($resource->isPreparationStepNeeded()) {
                $preparationSelect = $this->getFacetSelect($productCollection, $query,
                    $resource->getPreparationFilterCallback($facet), $resource->useDirectSelect());
                $resource->prepare($preparationSelect, $facet);
            }
            else {
                $preparationSelect = null;
            }

            $facetSelect = $this->getFacetSelect($productCollection, $query, $resource->getFilterCallback($facet),
                $resource->useDirectSelect());
            $facet->setData($resource->count(clone $facetSelect, $facet));
            $facet->setSelectedData($resource->getSelectedData($facetSelect, $facet));
        }

        foreach ($batches as $batch) {
            $resource = $this->facetResources->get($batch->getType());

            if ($resource->isPreparationStepNeeded()) {
                $resource->prepare($this->getFacetSelect($productCollection, $query), $batch);
            }

            $resource->count($this->getFacetSelect($productCollection, $query), $batch);
        }

        foreach ($query->getFacets() as $facet) {
            if (!$facet->getData()) {
                continue;
            }

            $resource = $this->facetResources->get($facet->getType());
            $resource->sort($facet);
        }

        $this->applyFilters($productCollection, $query);
        if ($searchFilter = $query->getFilters()->getOperand('search')) {
            $this->applyFilterToSelectRecursively($productCollection->getSelect(), $searchFilter);
        }
    }

    /**
     * @param Select $select
     * @param Filter $filter
     * @param callable $callback
     * @return false|string
     */
    public function applyFilterToSelectRecursively(Select $select, Filter $filter, $callback = null) {
        if ($callback && !call_user_func($callback, $filter)) {
            return false;
        }

        $resource = $this->filterResources->get($filter->getType());
        return $resource->apply($select, $filter, $callback);
    }

    protected function applyFiltersToSelect(Select $select, Query $query, $callback = null) {
        if ($condition = $this->applyFilterToSelectRecursively($select, $query->getFilters(), $callback)) {
            $select->where($condition);
        }
        $sql = $select->__toString();

        return $select;
    }

    /**
     * @param FacetBatch[] $batches
     * @param FacetResource $resource
     * @param Facet $facet
     * @return bool
     */
    protected function registerForBatchCounting(&$batches, $resource, $facet) {
        if (!$this->configuration->isBatchFilterCountingEnabled()) {
            return false;
        }

        if (!($batchType = $resource->getBatchType($facet))) {
            return false;
        }

        if (!isset($batches[$batchType])) {
            $batches[$batchType] = $this->factory->createFacetBatch($batchType);
        }
        $batches[$batchType]->addFacet($facet);

        return true;
    }

    /**
     * @param FullTextProductCollection $productCollection
     * @param Query $query
     */
    protected function applyFilters($productCollection, Query $query) {
        if ($this->configuration->useProductTempTable()) {
            $this->applyFiltersUsingTempTable($productCollection, $query);
        }
        else {
            $this->applyFiltersDirectly($productCollection, $query);
        }
    }

    /**
     * @param FullTextProductCollection $productCollection
     * @param Query $query
     */
    protected function applyFiltersUsingTempTable($productCollection, Query $query) {
        $select = $productCollection->getSelect();
        $tempTable = $this->getTempTable($productCollection, $query);
        $select->joinInner(['product_id_filter' => $tempTable],
            "`e`.`entity_id` = `product_id_filter`.`entity_id`", null);
    }

    /**
     * @param FullTextProductCollection $productCollection
     * @param Query $query
     */
    protected function applyFiltersDirectly($productCollection, Query $query) {
        $select = $productCollection->getSelect();
        $select->distinct();
        $this->applyFiltersToSelect($select, $query);
    }

    /**
     * @param FullTextProductCollection $productCollection
     * @param Query $query
     * @param callable|null $callback
     * @param bool $useDirectSelect
     * @return Select
     */
    protected function getFacetSelect($productCollection, Query $query, callable $callback = null, $useDirectSelect = false) {
        if ($this->configuration->useProductTempTable() && !$useDirectSelect) {
            return $this->getTempTableFacetSelect($productCollection, $query, $callback);
        }
        else {
            return $this->getDirectFacetSelect($productCollection, $query, $callback);
        }
    }

    /**
     * @param FullTextProductCollection $productCollection
     * @param Query $query
     * @param callable|null $callback
     * @return Select
     */
    protected function getTempTableFacetSelect($productCollection, Query $query, callable $callback = null) {
        $tempTable = $this->getTempTable($productCollection, $query, $callback);

        $db = $productCollection->getConnection();
        $select = $db->select()->from(['e' => $tempTable]);
        if (!$callback) {
            return $select;
        }

        $productCollection->joinPriceIndex($select);
        return $select;
    }

    /**
     * @param FullTextProductCollection $productCollection
     * @param Query $query
     * @param callable|null $callback
     * @return Select
     */
    protected function getDirectFacetSelect($productCollection, Query $query, callable $callback = null) {
        $select = clone $productCollection->getSelect();
        $select->distinct();
        $this->applyFiltersToSelect($select, $query, $callback);

        return $select;
    }

    /**
     * @param FullTextProductCollection $productCollection
     * @param Query $query
     * @param callable|null $callback
     * @return string
     */
    protected function getTempTable($productCollection, Query $query, callable $callback = null) {
        $tempTableSignature = $this->getRecursiveFilterSignature($query->getFilters(), $callback);
        if (!isset($this->tempTables[$tempTableSignature])) {
            $select = clone $productCollection->getSelect();
            $this->applyFiltersToSelect($select, $query, $callback);

            $this->tempTables[$tempTableSignature] = $productCollection->getTempIdTable($select);
        }

        return $this->tempTables[$tempTableSignature];
    }

    /**
     * @param Filter $filter
     * @param callable|null $callback
     * @return string
     */
    public function getRecursiveFilterSignature($filter, callable $callback = null) {
        /* @var Filter $filter */
        if ($callback && !call_user_func($callback, $filter)) {
            return '';
        }

        $resource = $this->filterResources->get($filter->getType());

        return $resource->getSignature($filter, $callback);
    }

    public function isEnabled() {
        return true;
    }
}