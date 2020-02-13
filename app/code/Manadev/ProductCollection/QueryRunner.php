<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\ProductCollection;

use Manadev\ProductCollection\Contracts\Filter;
use Manadev\ProductCollection\Contracts\QueryEngine;
use Manadev\ProductCollection\Registries\QueryEngines;
use Manadev\ProductCollection\Contracts\ProductCollection;

class QueryRunner
{
    const DEFAULT_QUERY_ENGINE = 'mysql';

    /**
     * @var Configuration
     */
    protected $configuration;
    /**
     * @var QueryEngines
     */
    protected $queryEngines;

    /**
     * @var QueryEngine
     */
    protected $engine;

    public function __construct(Configuration $configuration, QueryEngines $queryEngines) {

        $this->configuration = $configuration;
        $this->queryEngines = $queryEngines;
    }

    public function getQueryEngineNameFor(Query $query) {
        $engineName = $this->configuration->getQueryEngine();
        if ($engineName == static::DEFAULT_QUERY_ENGINE) {
            return $engineName;
        }

        if (!($engine = $this->queryEngines->get($engineName))) {
            return static::DEFAULT_QUERY_ENGINE;
        }

        $filterResourceRegistry = $engine->getSupportedFilters();
        $supportedFilterTypes = array_keys($filterResourceRegistry->getList());
        $isQuerySupportedByEngine = true;

        $query->eachFilter(function(Filter $filter) use ($filterResourceRegistry, $supportedFilterTypes,
            &$isQuerySupportedByEngine)
        {
            if (!in_array($filter->getType(), $supportedFilterTypes)) {
                $isQuerySupportedByEngine = false;
                return false; // break;
            }
            $filterResource = $filterResourceRegistry->get($filter->getType());
            if (!$filterResource->supports($filter)) {
                $isQuerySupportedByEngine = false;
                return false; // break;
            }

            return true; // continue;
        });

        if (!$isQuerySupportedByEngine) {
            return static::DEFAULT_QUERY_ENGINE;
        }

        return $engineName;
    }

    public function getEngine() {
        return $this->engine;
    }

    public function run(ProductCollection $productCollection) {
        $engineName = $this->getQueryEngineNameFor($productCollection->getQuery());
        $this->engine = $this->queryEngines->get($engineName);
        $this->engine->run($productCollection);
    }
}