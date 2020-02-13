<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\ProductCollection\Resources\Facets\Dropdown;

use Magento\Framework\DB\Select;
use Manadev\ProductCollection\Configuration;
use Manadev\ProductCollection\Contracts\Facet;
use Manadev\ProductCollection\Contracts\FacetResource;
use Manadev\ProductCollection\Facets\Dropdown\StandardFacet;
use Manadev\ProductCollection\FacetSorter;
use Manadev\ProductCollection\Resources\HelperResource;
use Zend_Db_Expr;
use Magento\Store\Model\StoreManagerInterface;
use Manadev\ProductCollection\Factory;
use Magento\Framework\Model\ResourceModel\Db;
use Magento\Eav\Model\Config;

class StandardFacetResource extends FacetResource
{
    /**
     * @var Config
     */
    protected $config;
    /**
     * @var FacetSorter
     */
    protected $sorter;

    public function __construct(Db\Context $context, Factory $factory,
        StoreManagerInterface $storeManager, Configuration $configuration,
        HelperResource $helperResource, Config $config,
        FacetSorter $sorter, $resourcePrefix = null)
    {
        parent::__construct($context, $factory, $storeManager, $configuration, $helperResource, $resourcePrefix);
        $this->config = $config;
        $this->sorter = $sorter;
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
        /* @var $facet StandardFacet */
        $this->prepareSelect($select, $facet);
        $counts = $this->getConnection()->fetchPairs($select);

        return $this->populateOptions($facet, $counts);
    }

    public function populateOptions(Facet $facet, $counts) {
        /* @var $facet StandardFacet */
        $attribute = $this->config->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $facet->getAttributeId());
        $options = $attribute->getFrontend()->getSelectOptions();
        if ($facet->getHideWithSingleVisibleItem() && count($options) < 2) {
            return false;
        }
        $selectedOptionIds = $facet->getSelectedOptionIds();
        $additionalData = $this->getAdditionalData($options);

        $emptyOptionSortOrder = false;
        foreach ($options as $sortOrder => &$option) {
            if ($option['value'] === '' && trim($option['label']) === '') {
                $emptyOptionSortOrder = $sortOrder;
                continue;
            }

            if (isset($additionalData[$option['value']])) {
                $option = array_merge($option, $additionalData[$option['value']]);
            }

            $option['count'] = isset($counts[$option['value']]) ? $counts[$option['value']] : 0;
            $option['is_selected'] = $selectedOptionIds !== false ? in_array($option['value'], $selectedOptionIds) : false;
            $option['sort_order'] = $sortOrder;
        }

        if ($emptyOptionSortOrder !== false) {
            unset($options[$emptyOptionSortOrder]);
        }

        return $options;
    }

    public function prepareSelect(Select $select, StandardFacet $facet) {
        $this->helperResource->clearFacetSelect($select);

        $fields = $this->getFields($facet);
        $select->columns($fields);
        $this->addJoins($select, $facet);
        $select->group($fields);

        return $select;
    }

    public function getFilterCallback(Facet $facet) {
        return $this->helperResource->dontApplyLogicalOrFilterNamed($facet->getName());
    }

    public function getFields(StandardFacet $facet) {
        return [
            'value' => new Zend_Db_Expr("`eav`.`value`"),
        ];
    }

    public function addJoins(Select $select, StandardFacet $facet) {
        $db = $this->getConnection();

        $select
            ->joinInner(array('eav' => $this->getTable('catalog_product_index_eav')),
                "`eav`.`entity_id` = `e`.`entity_id` AND
                {$db->quoteInto("`eav`.`attribute_id` = ?", $facet->getAttributeId())} AND
                {$db->quoteInto("`eav`.`store_id` = ?", $this->getStoreId())}",
                ['count' => "COUNT(DISTINCT `eav`.`entity_id`)"]
            );

        $this->helperResource->checkStockStatus($select, 'eav');

        return $select;
    }

    public function sort(Facet $facet) {
        $data = $facet->getData();
        $this->sorter->sort($facet, $data);
        $facet->setData($data);
    }

    public function getAdditionalData($options) {
        return [];
    }
}