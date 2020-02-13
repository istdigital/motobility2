<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\ProductCollection\Resources\Facets\Dropdown;

use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db;
use Magento\Store\Model\StoreManagerInterface;
use Manadev\Core\Resources\TemporaryResource;
use Manadev\ProductCollection\Configuration;
use Manadev\ProductCollection\Contracts\Facet;
use Manadev\ProductCollection\Contracts\FacetResource;
use Manadev\ProductCollection\Facets\Dropdown\OptimizedFacet;
use Manadev\ProductCollection\FacetSorter;
use Manadev\ProductCollection\Factory;
use Manadev\ProductCollection\Resources\HelperResource;
use Zend_Db_Expr;
use Magento\Framework\DB\Ddl\Table;

class OptimizedFacetResource extends FacetResource
{
    /**
     * @var FacetSorter
     */
    protected $sorter;
    /**
     * @var StandardFacetResource
     */
    protected $standardFacetResource;
    /**
     * @var TemporaryResource
     */
    protected $temporaryResource;

    public function __construct(Db\Context $context, Factory $factory,
        StoreManagerInterface $storeManager, Configuration $configuration,
        HelperResource $helperResource, FacetSorter $sorter, StandardFacetResource $standardFacetResource,
        TemporaryResource $temporaryResource,
        $resourcePrefix = null)
    {
        parent::__construct($context, $factory, $storeManager, $configuration,
            $helperResource, $resourcePrefix);
        $this->sorter = $sorter;
        $this->standardFacetResource = $standardFacetResource;
        $this->temporaryResource = $temporaryResource;
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct() {
        $this->_setMainTable('catalog_product_index_eav');
    }

    /**
     * @param Select $select
     * @param Facet $facet
     * @return mixed
     */
    public function count(Select $select, Facet $facet) {
        /* @var $facet OptimizedFacet */
        $this->prepareSelect($select, $facet);
        $result = $this->getConnection()->fetchAll($select);
        $minimumOptionCount = $facet->getHideWithSingleVisibleItem() ? 2 : 1;
        return count($result) >= $minimumOptionCount ? $result : false;
    }

    public function prepareSelect(Select &$select, Facet $facet) {
        /* @var $facet OptimizedFacet */
        $this->helperResource->clearFacetSelect($select);

        if (!$this->configuration->useCountTempTable()) {
            $this->joinEavIndex($select, $facet);
        }
        else {
            $db = $this->getConnection();
            $countTable = $this->temporaryResource->createOptionCountTable([$this, 'defineTempFields']);

            $this->joinEavIndex($select, $facet);
            $tempFields = $this->getTempFields($facet);
            $select->columns($tempFields);
            $select->group($tempFields);
            $db->query($select->insertIgnoreFromSelect($countTable,
                array_merge(['count'], array_keys($tempFields))));

            $select = $db->select()->from(['eav' => $countTable], ['count']);
        }

        $fields = $this->getFields($facet);
        $this->addJoins($select, $facet);
        $select->columns($fields);

        if (!$this->configuration->useCountTempTable()) {
            $select->group($fields);
        }

        return $select;
    }

    public function getFilterCallback(Facet $facet) {
        return $this->helperResource->dontApplyLogicalOrFilterNamed($facet->getName());
    }

    public function getFields(Facet $facet) {
        /* @var $facet OptimizedFacet */
        $selectedOptionIds = $facet->getSelectedOptionIds();
        $isSelectedExpr = $selectedOptionIds !== false
            ? "`eav`.`value` IN (" . implode(',', $selectedOptionIds). ")"
            : "1 <> 1";

        return [
            'sort_order' => new Zend_Db_Expr("`o`.`sort_order`"),
            'value' => new Zend_Db_Expr("`eav`.`value`"),
            'label' => new Zend_Db_Expr("COALESCE(`vs`.`value`, `vg`.`value`)"),
            'is_selected' => new Zend_Db_Expr($isSelectedExpr),
        ];
    }

    public function addJoins(Select $select, Facet $facet) {
        $this->joinOptions($select);
    }

    /**
     * @param Facet $facet
     * @return string|null
     */
    public function getBatchType($facet) {
        /* @var $facet OptimizedFacet */
        if ($facet->getSelectedOptionIds()) {
            return null;
        }

        return 'dropdown_optimized_batch';
    }

    protected function joinOptions(Select $select) {
        $db = $this->getConnection();
        $select
            ->joinInner(array('o' => $this->getTable('eav_attribute_option')),
                "`o`.`option_id` = `eav`.`value`", null)
            ->joinInner(array('vg' => $this->getTable('eav_attribute_option_value')),
                $db->quoteInto("`vg`.`option_id` = `eav`.`value` AND `vg`.`store_id` = ?", 0), null)
            ->joinLeft(array('vs' => $this->getTable('eav_attribute_option_value')),
                $db->quoteInto("`vs`.`option_id` = `eav`.`value` AND `vs`.`store_id` = ?", $this->getStoreId()), null);
    }

    protected function joinEavIndex(Select $select, $facet, $distinct = 'DISTINCT') {
        /* @var OptimizedFacet $facet */
        $db = $this->getConnection();

        $select
            ->joinInner(array('eav' => $this->getTable('catalog_product_index_eav')),
                "`eav`.`entity_id` = `e`.`entity_id` AND
                {$db->quoteInto("`eav`.`attribute_id` = ?", $facet->getAttributeId())} AND
                {$db->quoteInto("`eav`.`store_id` = ?", $this->getStoreId())}",
                array('count' => "COUNT($distinct `eav`.`entity_id`)")
            );

        $this->helperResource->checkStockStatus($select, 'eav');
    }

    public function sort(Facet $facet) {
        $data = $facet->getData();
        $this->sorter->sort($facet, $data);
        $facet->setData($data);
    }

    /**
     * @param Select $select
     * @param Facet  $facet
     *
     * @return mixed
     */
    public function getSelectedData(Select $select, Facet $facet) {
        /* @var $facet OptimizedFacet */
        $selectedOptionIds = $facet->getSelectedOptionIds();
        if (empty($selectedOptionIds)) {
            return false;
        }

        if ($facet->getData() !== false) {
            foreach ($facet->getData() as $item) {
                if (($index = array_search($item['value'], $selectedOptionIds)) !== false) {
                    unset($selectedOptionIds[$index]);
                }
            }
        }

        if (empty($selectedOptionIds)) {
            return false;
        }

        if (($data = $this->standardFacetResource->count($select, $this->factory->createStandardDropdownFacet(
            $facet->getName(), $facet->getAttributeId(), $selectedOptionIds, false,
            $facet->isShowSelectedOptionsFirst(), $facet->getSortBy()))) === false)
        {
            return false;
        }

        return array_values(array_filter($data, function($item) {
            return $item['is_selected'];
        }));
    }

    protected function getTempFields(Facet $facet) {
        return [
            'value' => new Zend_Db_Expr("`eav`.`value`")
        ];
    }

    public function defineTempFields(Table $table) {
    }
}