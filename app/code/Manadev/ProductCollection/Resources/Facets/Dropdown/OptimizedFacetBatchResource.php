<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\ProductCollection\Resources\Facets\Dropdown;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db;
use Magento\Store\Model\StoreManagerInterface;
use Manadev\Core\Helpers\DbHelper;
use Manadev\Core\Resources\TemporaryResource;
use Manadev\ProductCollection\Configuration;
use Manadev\ProductCollection\Contracts\Facet;
use Manadev\ProductCollection\Contracts\FacetBatch;
use Manadev\ProductCollection\Facets\Dropdown\OptimizedFacet;
use Manadev\ProductCollection\FacetSorter;
use Manadev\ProductCollection\Factory;
use Manadev\ProductCollection\Resources\HelperResource;

class OptimizedFacetBatchResource extends OptimizedFacetResource {
    /**
     * @var DbHelper
     */
    protected $dbHelper;

    public function __construct(Db\Context $context, Factory $factory, StoreManagerInterface $storeManager,
        Configuration $configuration, HelperResource $helperResource, DbHelper $dbHelper, FacetSorter $sorter,
        StandardFacetResource $standardFacetResource, TemporaryResource $temporaryResource, $resourcePrefix = null)
    {
        parent::__construct($context, $factory, $storeManager, $configuration, $helperResource, $sorter,
            $standardFacetResource, $temporaryResource, $resourcePrefix);
        $this->dbHelper = $dbHelper;
    }

    protected function joinEavIndex(Select $select, $facet, $distinct = 'DISTINCT') {
        /* @var FacetBatch $facet */
        $db = $this->getConnection();


        $select
            ->joinInner(array('eav' => $this->getTable('catalog_product_index_eav')),
                "`eav`.`entity_id` = `e`.`entity_id` AND
                {$db->quoteInto("`eav`.`attribute_id` IN (?)", $facet->getAttributeIds())} AND
                {$db->quoteInto("`eav`.`store_id` = ?", $this->getStoreId())}",
                array('count' => "COUNT($distinct `eav`.`entity_id`)")
            );

        $this->helperResource->checkStockStatus($select, 'eav');
    }

    public function getFields(Facet $facet) {
        return array_merge([
            'attribute_id' => new \Zend_Db_Expr("`eav`.`attribute_id`"),
        ], parent::getFields($facet));
    }

    public function getTempFields(Facet $facet) {
        return array_merge([
            'attribute_id' => new \Zend_Db_Expr("`eav`.`attribute_id`"),
        ], parent::getTempFields($facet));
    }

    public function defineTempFields(Table $table) {
        parent::defineTempFields($table);

        $db = $this->getConnection();

        $table
            ->addColumn('attribute_id', Table::TYPE_SMALLINT, null,
                ['unsigned' => true, 'nullable' => false])
            ->addIndex($db->getIndexName($this->getTable($table->getName()), ['attribute_id']),
                ['attribute_id']);

    }

    public function count(Select $select, Facet $facet) {
        /* @var FacetBatch $facet */
        $this->prepareSelect($select, $facet);
        foreach ($this->dbHelper->fetchAllPaged($this->getConnection(), $select) as $record) {
            $facet->getFacet($record)->addRecord($record);
        }
        foreach ($facet->getFacets() as $individualFacet) {
            /* @var OptimizedFacet $individualFacet */
            $minimumOptionCount = $individualFacet->getHideWithSingleVisibleItem() ? 2 : 1;
            if (!($data = $individualFacet->getData())) {
                continue;
            }

            if (count($individualFacet->getData()) >= $minimumOptionCount) {
                continue;
            }

            $individualFacet->setData(false);
        }
    }
}