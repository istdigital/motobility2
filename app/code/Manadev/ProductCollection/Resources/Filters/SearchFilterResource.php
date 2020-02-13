<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\ProductCollection\Resources\Filters;

use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorage;
use Manadev\ProductCollection\Contracts\Filter;
use Manadev\ProductCollection\Contracts\FilterResource;
use Manadev\ProductCollection\Filters\SearchFilter;
use Magento\Store\Model\StoreManagerInterface;
use Manadev\ProductCollection\Factory;
use Magento\Framework\Model\ResourceModel\Db;
use Manadev\ProductCollection\Resources\HelperResource;

class SearchFilterResource extends FilterResource
{
    /**
     * @var \Magento\Search\Model\SearchEngine
     */
    protected $searchEngine;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory
     */
    protected $temporaryStorageFactory;
    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Mapper
     */
    protected $mapper;

    protected $resultTables = [];

    public function __construct(Db\Context $context, Factory $factory,
        StoreManagerInterface $storeManager, HelperResource $helperResource,
        \Magento\Search\Model\SearchEngine $searchEngine,
        \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory $temporaryStorageFactory,
        \Magento\Framework\Search\Adapter\Mysql\Mapper $mapper,
        $resourcePrefix = null)
    {
        parent::__construct($context, $factory, $storeManager, $helperResource, $resourcePrefix);
        $this->searchEngine = $searchEngine;
        $this->temporaryStorageFactory = $temporaryStorageFactory;
        $this->mapper = $mapper;
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct() {
        $this->_setMainTable('catalogsearch_fulltext_scope' . $this->getStoreId());
    }


    /**
     * @param Select $select
     * @param Filter $filter
     * @param $callback
     * @return false|string|void
     * @throws \Zend_Db_Exception
     */
    public function apply(Select $select, Filter $filter, $callback) {
        /** @var $filter SearchFilter */

        $key = $this->getStoreId() . '-' . $filter->getText();
        if (!isset($this->resultTables[$key])) {
            $requestBuilder = $this->factory->createRequestBuilder();
            $requestBuilder->bindDimension('scope', $this->getStoreId());
            $requestBuilder->bind('search_term', $filter->getText());
            $requestBuilder->setRequestName('quick_search_container');
            $request = $requestBuilder->create();

            $query = $this->mapper->buildQuery($request);
            $temporaryStorage = $this->temporaryStorageFactory->create();
            $table = $temporaryStorage->storeDocumentsFromSelect($query);

            $this->resultTables[$key] = $table->getName();
        }

        $select->joinInner(['search_result' => $this->resultTables[$key]],
            'e.entity_id = search_result.' . TemporaryStorage::FIELD_ENTITY_ID, []);
    }
}