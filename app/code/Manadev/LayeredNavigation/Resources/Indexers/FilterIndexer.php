<?php
/** 
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation\Resources\Indexers;

use Exception;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Model\ResourceModel\Db;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Manadev\Core\QueryLogger;
use Manadev\LayeredNavigation\Models\Filter;
use Manadev\LayeredNavigation\Registries\FilterIndexers\PrimaryFilterIndexers;
use Manadev\LayeredNavigation\Registries\FilterIndexers\SecondaryFilterIndexers;
use Psr\Log\LoggerInterface as Logger;
use Manadev\LayeredNavigation\Configuration;
use Manadev\LayeredNavigation\Resources\Indexers\Filter\IndexerScope;
use Zend_Db_Expr;

class FilterIndexer extends Db\AbstractDb {
    protected $usedStoreConfigPaths;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var PrimaryFilterIndexers
     */
    protected $primaryFilterIndexers;
    /**
     * @var IndexerScope
     */
    protected $scope;
    /**
     * @var Configuration
     */
    protected $configuration;
    /**
     * @var QueryLogger
     */
    protected $queryLogger;
    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var TypeListInterface
     */
    protected $cacheTypeList;
    /**
     * @var SecondaryFilterIndexers
     */
    protected $secondaryFilterIndexers;

    public function __construct(Db\Context $context, StoreManagerInterface $storeManager,
        PrimaryFilterIndexers $primaryFilterIndexers, SecondaryFilterIndexers $secondaryFilterIndexers,
        IndexerScope $scope, Configuration $configuration, QueryLogger $queryLogger,
        Logger $logger, TypeListInterface $cacheTypeList, $resourcePrefix = null)
    {
        parent::__construct($context, $resourcePrefix);

        $this->storeManager = $storeManager;
        $this->primaryFilterIndexers = $primaryFilterIndexers;
        $this->scope = $scope;
        $this->configuration = $configuration;
        $this->queryLogger = $queryLogger;
        $this->logger = $logger;
        $this->cacheTypeList = $cacheTypeList;
        $this->secondaryFilterIndexers = $secondaryFilterIndexers;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct() {
        $this->_setMainTable('mana_filter');
    }

    public function getUsedStoreConfigPaths() {
        if (!$this->usedStoreConfigPaths) {
            $this->usedStoreConfigPaths = [];

            foreach ($this->primaryFilterIndexers->getList() as $indexer) {
                $this->usedStoreConfigPaths = array_merge($this->usedStoreConfigPaths, $indexer->getUsedStoreConfigPaths());
            }
            foreach ($this->secondaryFilterIndexers->getList() as $indexer) {
                $this->usedStoreConfigPaths = array_merge($this->usedStoreConfigPaths, $indexer->getUsedStoreConfigPaths());
            }

            $this->usedStoreConfigPaths = array_flip($this->usedStoreConfigPaths);
        }

        return $this->usedStoreConfigPaths;
    }


    /**
     * Indexes all filter settings on global and store level, depending on
     * `$storeId` parameter.
     *
     * @param int $storeId If 0, global and store level settings are indexed,
     *                     otherwise only settings on specified store level
     *                     are indexed.
     * @param bool $useTransaction
     * @throws Exception
     */
    public function reindexAll($storeId = 0, $useTransaction = true) {
        $this->index(['all', 'store' => $storeId], $useTransaction);
    }

    /**
     * Called when attribute is changed. Indexes filters settings inherited
     * from specified attribute on global and store level, depending on
     * `$storeId` parameter.
     *
     * @param array|bool $ids
     * @param int $storeId If 0, global and store level settings are indexed,
     *                     otherwise only settings on specified store level
     *                     are indexed.
     * @param bool $useTransaction
     * @throws Exception
     */
    public function reindexChangedAttributes($ids = false, $storeId = 0,
        $useTransaction = true)
    {
        $this->index(['attributes' => $ids, 'store' => $storeId],
            $useTransaction);
    }

    /**
     * Called when filter is changed. Indexes settings of specified filter on
     * global and store level, depending on `$storeId` parameter.
     *
     * @param int[] $ids
     * @param int $storeId If 0, global and store level settings are indexed,
     *                     otherwise only settings on specified store level
     *                     are indexed.
     * @param bool $useTransaction
     * @throws Exception
     */
    public function reindexChangedFilters($ids, $storeId = 0,
        $useTransaction = true)
    {
        $this->index(['filters' => $ids, 'store' => $storeId], $useTransaction);
    }

    /**
     * @param $model Filter
     * @return array
     */
    public function loadDefaults($model) {
        $result = [];
        $changes = [
            'filters' => [$model->getData('filter_id') => "'" . $model->getData('unique_key') . "'"],
            'store' => $model->getData('store_id'),
            'load_defaults' => true,
        ];

        if (!$model->getData('store_id')) {
            foreach ($this->primaryFilterIndexers->getList() as $indexer) {
                $this->mergeDefaults($result, $indexer->index($changes));
            }

            foreach ($this->secondaryFilterIndexers->getList() as $indexer) {
                $this->mergeDefaults($result, $indexer->index($changes));
            }
        }
        else {
            $this->mergeDefaults($result, $this->indexForStore($this->storeManager->getStore($changes['store']), $changes));
        }

        return $model->getResource()->filterData($model, $result);
    }


    protected function mergeDefaults(&$result, $select) {
        $db = $this->getConnection();

        if (!$select) {
            return;
        }

        $data = $db->fetchRow($select);
        if (!empty($data)) {
            $result = array_merge($result, $data);
        }
    }

    protected function index($changes = ['all'], $useTransaction = true) {
        if ($this->configuration->isFilterIndexQueryLoggingEnabled()) {
            $this->queryLogger->begin('filter-index');
        }
        // Clear config cache if config is not set
        if(is_null($this->configuration->getDefaultShowInMainSidebar())) {
            $this->cacheTypeList->cleanType('config');
            throw new Exception('Manadev_LayeredNavigation config is not yet set. Please try again.');
        }

        $db = $this->getConnection();

        if ($useTransaction) {
            $db->beginTransaction();
        }

        try {
            if (empty($changes['store'])) {
                $this->markGlobalRowsAsDeleted($changes);

                foreach($this->primaryFilterIndexers->getList() as $indexer) {
                    $indexer->index($changes);
                }

                foreach($this->secondaryFilterIndexers->getList() as $indexer) {
                    $indexer->index($changes);
                }

                $this->deleteRowsMarkedForDeletion($changes);

                $this->assignGlobalIds($changes);

                foreach($this->storeManager->getStores() as $store) {
                    $this->indexForStore($store, $changes);
                }
            }
            else {
                $this->indexForStore($this->storeManager->getStore($changes['store']), $changes);
            }

            if ($useTransaction) {
                $db->commit();
            }
        }
        catch (Exception $e) {
            $this->logger->critical($e);
            if ($useTransaction) {
                $db->rollBack();
            }

            throw $e;
        }
        finally {
            if ($this->configuration->isFilterIndexQueryLoggingEnabled()) {
                $this->queryLogger->end('filter-index');
            }
        }
    }

    protected function markGlobalRowsAsDeleted($changes) {
        $db = $this->getConnection();

        $db->update($this->getMainTable(), ['is_deleted' => 1],
            $this->scope->limitMarkingForDeletion($changes));
    }

    protected function deleteRowsMarkedForDeletion($changes) {
        $db = $this->getConnection();

        $db->delete($this->getMainTable(), $this->scope->limitDeletion($changes));
    }

    protected function assignGlobalIds($changes) {
        $db = $this->getConnection();

        $db->update($this->getMainTable(), ['filter_id' => new Zend_Db_Expr("`id`")],
            $this->scope->limitIdAssignment($changes));
    }

    /**
     * @param Store|StoreInterface $store
     * @param array $changes
     * @return \Magento\Framework\DB\Select
     */
    protected function indexForStore($store, $changes = ['all']) {
        $db = $this->getConnection();

        if (empty($changes['load_defaults'])) {
            $fields = [
                'edit_id' => new Zend_Db_Expr("`fse`.`id`"),
                'filter_id' => new Zend_Db_Expr("`fg`.`id`"),
                'store_id' => new Zend_Db_Expr($store->getId()),
                'is_deleted' => new Zend_Db_Expr("0"),
                'attribute_id' => new Zend_Db_Expr("`fg`.`attribute_id`"),
                'attribute_code' => new Zend_Db_Expr("`fg`.`attribute_code`"),
                'swatch_input_type' => new Zend_Db_Expr("`fg`.`swatch_input_type`"),
                'unique_key' => new Zend_Db_Expr("CONCAT(`fg`.`unique_key`, '-{$store->getId()}')"),
                'param_name' => new Zend_Db_Expr("COALESCE(`fse`.`param_name`, `fg`.`param_name`)"),
                'type' => new Zend_Db_Expr("`fg`.`type`"),

                'title' => new Zend_Db_Expr("COALESCE(`fse`.`title`, `al`.`value`, `fg`.`title`)"),
                'position' => new Zend_Db_Expr("COALESCE(`fse`.`position`, `fg`.`position`)"),
                'template' => new Zend_Db_Expr("COALESCE(`fse`.`template`, `fg`.`template`)"),
                'show_in_main_sidebar' => new Zend_Db_Expr("COALESCE(`fse`.`show_in_main_sidebar`, `fg`.`show_in_main_sidebar`)"),
                'show_in_additional_sidebar' => new Zend_Db_Expr("COALESCE(`fse`.`show_in_additional_sidebar`, `fg`.`show_in_additional_sidebar`)"),
                'show_above_products' => new Zend_Db_Expr("COALESCE(`fse`.`show_above_products`, `fg`.`show_above_products`)"),
                'show_on_mobile' => new Zend_Db_Expr("COALESCE(`fse`.`show_on_mobile`, `fg`.`show_on_mobile`)"),
                'is_enabled_in_categories' => new Zend_Db_Expr("COALESCE(`fse`.`is_enabled_in_categories`, `fg`.`is_enabled_in_categories`)"),
                'is_enabled_in_search' => new Zend_Db_Expr("COALESCE(`fse`.`is_enabled_in_search`, `fg`.`is_enabled_in_search`)"),
                'minimum_product_count_per_option' => new Zend_Db_Expr("COALESCE(`fse`.`minimum_product_count_per_option`,
                    `fg`.`minimum_product_count_per_option`)"),

                'calculate_slider_min_max_based_on' => new Zend_Db_Expr("COALESCE(`fse`.`calculate_slider_min_max_based_on`,
                    `fg`.`calculate_slider_min_max_based_on`)"),
                'number_format' => new Zend_Db_Expr("COALESCE(`fse`.`number_format`, `fg`.`number_format`)"),
                'decimal_digits' => new Zend_Db_Expr("COALESCE(`fse`.`decimal_digits`, `fg`.`decimal_digits`)"),
                'is_two_number_formats' => new Zend_Db_Expr("COALESCE(`fse`.`is_two_number_formats`, `fg`.`is_two_number_formats`)"),
                'use_second_number_format_on' => new Zend_Db_Expr("COALESCE(`fse`.`use_second_number_format_on`, `fg`.`use_second_number_format_on`)"),
                'second_number_format' => new Zend_Db_Expr("COALESCE(`fse`.`second_number_format`, `fg`.`second_number_format`)"),
                'second_decimal_digits' => new Zend_Db_Expr("COALESCE(`fse`.`second_decimal_digits`, `fg`.`second_decimal_digits`)"),
                'show_thousand_separator' => new Zend_Db_Expr("COALESCE(`fse`.`show_thousand_separator`, `fg`.`show_thousand_separator`)"),
                'is_slide_on_existing_values' => new Zend_Db_Expr("COALESCE(`fse`.`is_slide_on_existing_values`, `fg`.`is_slide_on_existing_values`)"),
                'is_manual_range' => new Zend_Db_Expr("COALESCE(`fse`.`is_manual_range`, `fg`.`is_manual_range`)"),
                'slider_style' => new Zend_Db_Expr("COALESCE(`fse`.`slider_style`, `fg`.`slider_style`)"),
                'min_max_role' => new Zend_Db_Expr("COALESCE(`fse`.`min_max_role`, `fg`.`min_max_role`)"),
                'min_slider_code' => new Zend_Db_Expr("COALESCE(`fse`.`min_slider_code`, `fg`.`min_slider_code`)"),
                'hide_filter_with_single_visible_item' => new Zend_Db_Expr("COALESCE(`fse`.`hide_filter_with_single_visible_item`, `fg`.`hide_filter_with_single_visible_item`)"),

                'use_filter_title_in_url' => new Zend_Db_Expr("COALESCE(`fse`.`use_filter_title_in_url`, `fg`.`use_filter_title_in_url`)"),
                'url_part' => new Zend_Db_Expr("COALESCE(`fse`.`url_part`, `fg`.`url_part`)"),
                'position_in_url' => new Zend_Db_Expr("COALESCE(`fse`.`position_in_url`, `fg`.`position_in_url`)"),
                'include_in_canonical_url' => new Zend_Db_Expr("COALESCE(`fse`.`include_in_canonical_url`, `fg`.`include_in_canonical_url`)"),
                'force_no_index' => new Zend_Db_Expr("COALESCE(`fse`.`force_no_index`, `fg`.`force_no_index`)"),
                'force_no_follow' => new Zend_Db_Expr("COALESCE(`fse`.`force_no_follow`, `fg`.`force_no_follow`)"),
                'include_in_meta_title' => new Zend_Db_Expr("COALESCE(`fse`.`include_in_meta_title`, `fg`.`include_in_meta_title`)"),
                'include_in_meta_description' => new Zend_Db_Expr("COALESCE(`fse`.`include_in_meta_description`, `fg`.`include_in_meta_description`)"),
                'include_in_meta_keywords' => new Zend_Db_Expr("COALESCE(`fse`.`include_in_meta_keywords`, `fg`.`include_in_meta_keywords`)"),
                'include_in_sitemap' => new Zend_Db_Expr("COALESCE(`fse`.`include_in_sitemap`, `fg`.`include_in_sitemap`)"),

                'show_selected_options_first' => new Zend_Db_Expr("COALESCE(`fse`.`show_selected_options_first`, `fg`.`show_selected_options_first`)"),
                'sort_options_by' => new Zend_Db_Expr("COALESCE(`fse`.`sort_options_by`, `fg`.`sort_options_by`)"),

                'show_more_method' => new Zend_Db_Expr("COALESCE(`fse`.`show_more_method`, `fg`.`show_more_method`)"),
                'show_more_item_limit' => new Zend_Db_Expr("COALESCE(`fse`.`show_more_item_limit`, `fg`.`show_more_item_limit`)"),
                'show_option_search' => new Zend_Db_Expr("COALESCE(`fse`.`show_option_search`, `fg`.`show_option_search`)"),

                'use_logical_and' => new Zend_Db_Expr("COALESCE(`fse`.`use_logical_and`, `fg`.`use_logical_and`)"),
            ];
        }
        else {
            $fields = [
                'edit_id' => new Zend_Db_Expr("NULL"),
                'filter_id' => new Zend_Db_Expr("`fg`.`id`"),
                'store_id' => new Zend_Db_Expr($store->getId()),
                'is_deleted' => new Zend_Db_Expr("0"),
                'attribute_id' => new Zend_Db_Expr("`fg`.`attribute_id`"),
                'attribute_code' => new Zend_Db_Expr("`fg`.`attribute_code`"),
                'swatch_input_type' => new Zend_Db_Expr("`fg`.`swatch_input_type`"),
                'unique_key' => new Zend_Db_Expr("CONCAT(`fg`.`unique_key`, '-{$store->getId()}')"),
                'param_name' => new Zend_Db_Expr("`fg`.`param_name`"),
                'type' => new Zend_Db_Expr("`fg`.`type`"),

                'title' => new Zend_Db_Expr("COALESCE(`al`.`value`, `fg`.`title`)"),
                'position' => new Zend_Db_Expr("`fg`.`position`"),
                'template' => new Zend_Db_Expr("`fg`.`template`"),
                'show_in_main_sidebar' => new Zend_Db_Expr("`fg`.`show_in_main_sidebar`"),
                'show_in_additional_sidebar' => new Zend_Db_Expr("`fg`.`show_in_additional_sidebar`"),
                'show_above_products' => new Zend_Db_Expr("`fg`.`show_above_products`"),
                'show_on_mobile' => new Zend_Db_Expr("`fg`.`show_on_mobile`"),
                'is_enabled_in_categories' => new Zend_Db_Expr("`fg`.`is_enabled_in_categories`"),
                'is_enabled_in_search' => new Zend_Db_Expr("`fg`.`is_enabled_in_search`"),
                'minimum_product_count_per_option' => new Zend_Db_Expr("`fg`.`minimum_product_count_per_option`"),

                'calculate_slider_min_max_based_on' => new Zend_Db_Expr("`fg`.`calculate_slider_min_max_based_on`"),
                'number_format' => new Zend_Db_Expr("`fg`.`number_format`"),
                'decimal_digits' => new Zend_Db_Expr("`fg`.`decimal_digits`"),
                'is_two_number_formats' => new Zend_Db_Expr("`fg`.`is_two_number_formats`"),
                'use_second_number_format_on' => new Zend_Db_Expr("`fg`.`use_second_number_format_on`"),
                'second_number_format' => new Zend_Db_Expr("`fg`.`second_number_format`"),
                'second_decimal_digits' => new Zend_Db_Expr("`fg`.`second_decimal_digits`"),
                'show_thousand_separator' => new Zend_Db_Expr("`fg`.`show_thousand_separator`"),
                'is_slide_on_existing_values' => new Zend_Db_Expr("`fg`.`is_slide_on_existing_values`"),
                'is_manual_range' => new Zend_Db_Expr("`fg`.`is_manual_range`"),
                'slider_style' => new Zend_Db_Expr("`fg`.`slider_style`"),
                'min_max_role' => new Zend_Db_Expr("`fg`.`min_max_role`"),
                'min_slider_code' => new Zend_Db_Expr("`fg`.`min_slider_code`"),
                'hide_filter_with_single_visible_item' => new Zend_Db_Expr("`fg`.`hide_filter_with_single_visible_item`"),

                'use_filter_title_in_url' => new Zend_Db_Expr("`fg`.`use_filter_title_in_url`"),
                'url_part' => new Zend_Db_Expr("`fg`.`url_part`"),
                'position_in_url' => new Zend_Db_Expr("`fg`.`position_in_url`"),
                'include_in_canonical_url' => new Zend_Db_Expr("`fg`.`include_in_canonical_url`"),
                'force_no_index' => new Zend_Db_Expr("`fg`.`force_no_index`"),
                'force_no_follow' => new Zend_Db_Expr("`fg`.`force_no_follow`"),
                'include_in_meta_title' => new Zend_Db_Expr("`fg`.`include_in_meta_title`"),
                'include_in_meta_description' => new Zend_Db_Expr("`fg`.`include_in_meta_description`"),
                'include_in_meta_keywords' => new Zend_Db_Expr("`fg`.`include_in_meta_keywords`"),
                'include_in_sitemap' => new Zend_Db_Expr("`fg`.`include_in_sitemap`"),

                'show_selected_options_first' => new Zend_Db_Expr("`fg`.`show_selected_options_first`"),
                'sort_options_by' => new Zend_Db_Expr("`fg`.`sort_options_by`"),

                'show_more_method' => new Zend_Db_Expr("`fg`.`show_more_method`"),
                'show_more_item_limit' => new Zend_Db_Expr("`fg`.`show_more_item_limit`"),
                'show_option_search' => new Zend_Db_Expr("`fg`.`show_option_search`"),

                'use_logical_and' => new Zend_Db_Expr("`fg`.`use_logical_and`"),
            ];
        }

        $select = $db->select()
            ->distinct()
            ->from(['fg' => $this->getTable('mana_filter')], null)
            ->joinLeft(['fse' => $this->getTable('mana_filter_edit')],
                $db->quoteInto("`fse`.`filter_id` = `fg`.`id` AND `fse`.`store_id` = ?", $store->getId()), null)
            ->joinLeft(['a' => $this->getTable('eav_attribute')], "`a`.`attribute_id` = `fg`.`attribute_id`", null)
            ->joinLeft(['al' => $this->getTable('eav_attribute_label')],
                $db->quoteInto("`al`.`attribute_id` = `fg`.`attribute_id` AND `al`.`store_id` = ?", $store->getId()), null)
            ->columns($fields);

        if ($whereClause = $this->scope->limitStoreLevelIndexing($changes, $fields)) {
            $select->where($whereClause);
        }

        if (empty($changes['load_defaults'])) {
            // convert SELECT into UPDATE which acts as INSERT on DUPLICATE unique keys
            $sql = $select->insertFromSelect($this->getMainTable(), array_keys($fields));

            // run the statement
            $db->query($sql);
        }

        return $select;
    }
}