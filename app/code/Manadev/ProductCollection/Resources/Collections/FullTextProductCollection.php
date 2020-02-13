<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\ProductCollection\Resources\Collections;

use Magento\Framework\Search\Adapter\Mysql\TemporaryStorage;
use Manadev\Core\Features;
use Manadev\Core\Profiler;
use Manadev\Core\QueryLogger;
use Manadev\Core\Resources\TemporaryResource;
use Manadev\ProductCollection\Configuration;
use Manadev\ProductCollection\Contracts\Facet;
use Manadev\ProductCollection\Contracts\ProductCollection;
use Manadev\ProductCollection\FacetGenerator;
use Manadev\ProductCollection\Factory;
use Manadev\ProductCollection\FilterGenerator;
use Manadev\ProductCollection\Filters\SearchFilter;
use Manadev\ProductCollection\Query;
use Manadev\ProductCollection\QueryRunner;
use Magento\Framework\DB\Select;

class FullTextProductCollection extends \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection implements ProductCollection
{
    /**
     * @var QueryLogger
     */
    protected $queryLogger;

    /**
     * @var Query
     */
    protected $query;
    /**
     * @var QueryRunner
     */
    protected $queryRunner;
    /**
     * @var Factory
     */
    protected $factory;
    /**
     * @var FacetGenerator
     */
    protected $facetGenerator;
    /**
     * @var FilterGenerator
     */
    protected $filterGenerator;
    /**
     * @var Configuration
     */
    protected $configuration;
    /**
     * @var Features
     */
    protected $features;

    protected $facetsLoaded = false;
    /**
     * @var Profiler
     */
    protected $profiler;

    /**
     * @var TemporaryResource
     */
    protected $temporaryResource;


    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Eav\Model\EntityFactory $eavEntityFactory
     * @param \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Url $catalogUrl
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Customer\Api\GroupManagementInterface $groupManagement
     * @param \Magento\Search\Model\QueryFactory $catalogSearchData
     * @param \Magento\Framework\Search\Request\Builder $requestBuilder
     * @param \Magento\Search\Model\SearchEngine $searchEngine
     * @param \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory $temporaryStorageFactory
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param string $searchRequestName
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrl,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        \Magento\Search\Model\QueryFactory $catalogSearchData,
        \Magento\Framework\Search\Request\Builder $requestBuilder,
        \Magento\Search\Model\SearchEngine $searchEngine,
        \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory $temporaryStorageFactory,
        TemporaryResource $temporaryResource,
        QueryLogger $queryLogger,
        Factory $factory,
        QueryRunner $queryRunner,
        Features $features,
        FacetGenerator $facetGenerator,
        FilterGenerator $filterGenerator,
        Configuration $configuration,
        Profiler $profiler,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        $searchRequestName = 'catalog_view_container'
    ) {
        $this->queryLogger = $queryLogger;

        $this->query = $factory->createQuery();
        $this->query->setProductCollection($this);

        $this->queryRunner = $queryRunner;
        $this->factory = $factory;
        $this->features = $features;
        $facetGenerator->setCollection($this);
        $this->facetGenerator = $facetGenerator;
        $this->filterGenerator = $filterGenerator;
        $this->configuration = $configuration;
        $this->profiler = $profiler;
        $this->temporaryResource = $temporaryResource;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $eavConfig, $resource, $eavEntityFactory,
            $resourceHelper, $universalFactory, $storeManager, $moduleManager, $catalogProductFlatState, $scopeConfig, $productOptionFactory,
            $catalogUrl, $localeDate, $customerSession, $dateTime, $groupManagement, $catalogSearchData, $requestBuilder, $searchEngine,
            $temporaryStorageFactory, $connection, $searchRequestName
        );
    }

    public function load($printQuery = false, $logQuery = false) {
        if (!$this->features->isEnabled(__CLASS__)) {
            return parent::load($printQuery, $logQuery);
        }

        $this->profiler->start('product-collection');
        if ($this->configuration->isProductCollectionQueryLoggingEnabled()) {
            $this->queryLogger->begin('product-collection');
        }

        parent::load($printQuery, $logQuery);

        if ($this->configuration->isProductCollectionQueryLoggingEnabled()) {
            $this->queryLogger->end('product-collection');
        }
        $this->profiler->stop('product-collection');

        return $this;
    }

    protected function _renderFiltersBefore() {
        if (!$this->features->isEnabled(__CLASS__)) {
            parent::_renderFiltersBefore();
            return;
        }

        $this->loadFacets();
    }
    public function loadFacets() {
        if ($this->facetsLoaded) {
            return;
        }

        $this->profiler->start('facet-counting');

        if ($this->configuration->isFacetCountingQueryLoggingEnabled()) {
            $this->queryLogger->begin('facet-counting');
        }

        $this->queryRunner->run($this);

        if ($this->configuration->isFacetCountingQueryLoggingEnabled()) {
            $this->queryLogger->end('facet-counting');
        }

        $this->profiler->stop('facet-counting');

        $this->facetsLoaded = true;
    }

    /**
     * @return Query
     */
    public function getQuery() {
        return $this->query;
    }

    public function getFacetedData($field) {
        if (!$this->features->isEnabled(__CLASS__)) {
            return parent::getFacetedData($field);
        }

        $this->_renderFilters();
        return $this->facetGenerator->getFacetedData($field);
    }

    public function addFieldToFilter($field, $condition = null) {
        if (!$this->features->isEnabled(__CLASS__)) {
            return parent::addFieldToFilter($field, $condition);
        }

        $filterGroup = $this->query->getFilterGroup('productcollection');
        if(!$filterGroup->getOperand($field)) {
            $filter = $this->filterGenerator->getFilter($field, $condition);
            if($filter) {
                $filterGroup->addOperand($filter);
            }
        }
        return $this;
    }

    public function addCategoryFilter(\Magento\Catalog\Model\Category $category) {
        parent::addCategoryFilter($category);
        if (!$this->features->isEnabled(__CLASS__)) {
            return $this;
        }

        $this->query->setCategory($category);
        return $this;
    }

    /**
     * Add search query filter
     *
     * @param string $query
     * @return $this
     */
    public function addSearchFilter($query)
    {
        if (!$this->features->isEnabled(__CLASS__)) {
            return parent::addSearchFilter($query);
        }

        $self = $this;

        /* @var $searchFilter SearchFilter */
        $searchFilter = $this->query->getFilterGroup('search', function($name) use ($self) {
            return $self->factory->createSearchFilter($name);
        });

        $searchFilter->addSearchText($query);

        return parent::addSearchFilter($query);
    }

    /**
     * @param Select $select
     * @return string
     */
    public function getTempIdTable($select) {
        $tempIdTable = $this->temporaryResource->createProductIdTable();

        $select->reset(Select::ORDER);
        $select->reset(Select::LIMIT_COUNT);
        $select->reset(Select::LIMIT_OFFSET);
        $select->reset(Select::COLUMNS);

        $select->columns('e.' . $this->getEntity()->getIdFieldName());
        $select->resetJoinLeft();

        $this->getConnection()->query($select->insertIgnoreFromSelect($tempIdTable));

        return $tempIdTable;
    }

    public function joinPriceIndex(Select $select) {
        $db = $this->getConnection();

        $filters = $this->_productLimitationFilters;
        $select->joinInner(['price_index' => $this->getTable('catalog_product_index_price')],
            "`price_index`.`entity_id` = `e`.`entity_id` AND " .
            $db->quoteInto("`price_index`.`website_id` = ? AND ", $filters['website_id']) .
            $db->quoteInto("`price_index`.`customer_group_id` = ?", $filters['customer_group_id']), null);
    }

    protected function _renderOrders() {
        if (!$this->_isOrdersRendered) {
            $relevanceOrderDirection = null;
            $class = new \ReflectionClass('Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection');
            if ($class->hasProperty('relevanceOrderDirection')) {
                // Magento 2.3.1
                $property = $class->getProperty('relevanceOrderDirection');
                $property->setAccessible(true);
                $relevanceOrderDirection = $property->getValue($this);
            }
            elseif ($class->hasProperty('searchOrders')) {
                // Magento 2.3.2
                $property = $class->getProperty('searchOrders');
                $property->setAccessible(true);
                $relevanceOrderDirection = $property->getValue($this)['relevance'] ?? null;
            }
            if ($relevanceOrderDirection) {
                // only order by relevance if the system did search by search text. Searching by search
                // text could have skipped, for instance if search text is too short
                if (array_key_exists('search_result', $this->getSelect()->getPart('from'))) {
                    $this->getSelect()->order('search_result.'. TemporaryStorage::FIELD_SCORE . ' ' . $relevanceOrderDirection);
                }
            }
            else {
                parent::_renderOrders();
            }

            $this->addAttributeToSort('entity_id');
        }
        return $this;
    }

    /**
     * @param $select
     * @param $categoryCollection
     */
    public function addCountToCategoriesOnSelect($select, $categoryCollection) {
        $this->withSelect($select, function() use ($categoryCollection) {
            $this->addCountToCategories($categoryCollection);
        });
    }

    public function withSelect($select, callable $callback) {
        $originalSelect = $this->_select;
        $this->_select = $select;
        try {
            $callback();
        }
        finally {
            $this->_select = $originalSelect;
        }
    }
}