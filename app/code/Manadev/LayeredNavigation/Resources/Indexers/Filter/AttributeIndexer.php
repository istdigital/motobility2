<?php
/** 
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation\Resources\Indexers\Filter;

use Magento\Catalog\Model\Product;
use Magento\Framework\Model\ResourceModel\Db;
use Manadev\LayeredNavigation\Configuration;
use Manadev\LayeredNavigation\Contracts\FilterIndexer;
use Zend_Db_Expr;

abstract class AttributeIndexer extends Db\AbstractDb implements FilterIndexer {
    /**
     * @var IndexerScope
     */
    protected $scope;
    /**
     * @var Configuration
     */
    protected $configuration;

    public function __construct(Db\Context $context,
        Configuration $configuration, IndexerScope $scope,
        $resourcePrefix = null)
    {
        parent::__construct($context, $resourcePrefix);

        $this->scope = $scope;
        $this->configuration = $configuration;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct() {
        $this->_setMainTable('mana_filter');
    }

    /**
     * Returns array of store configuration paths which are used in `index`
     * method of this data source
     * @return string[]
     */
    public function getUsedStoreConfigPaths() {
        return [
            Configuration::DEFAULT_DROPDOWN_TEMPLATE,
            Configuration::DEFAULT_DECIMAL_TEMPLATE,
            Configuration::DEFAULT_SWATCH_TEMPLATE,
            Configuration::DEFAULT_SHOW_IN_MAIN_SIDEBAR,
            Configuration::DEFAULT_SHOW_IN_ADDITIONAL_SIDEBAR,
            Configuration::DEFAULT_SHOW_ABOVE_PRODUCTS,
            Configuration::DEFAULT_SHOW_ON_MOBILE,
            Configuration::CALCULATE_SLIDER_MIN_MAX_BASED_ON,
            Configuration::SLIDER_STYLE,
            Configuration::HIDE_FILTERS_WITH_SINGLE_VISIBLE_ITEM,

            Configuration::DEFAULT_USE_FILTER_TITLE_IN_URL,
            Configuration::DEFAULT_URL_PART,
            Configuration::DEFAULT_INCLUDE_IN_CANONICAL_URL,
            Configuration::DEFAULT_FORCE_NO_INDEX,
            Configuration::DEFAULT_FORCE_NO_FOLLOW,
            Configuration::DEFAULT_INCLUDE_IN_META_TITLE,
            Configuration::DEFAULT_INCLUDE_IN_META_DESCRIPTION,
            Configuration::DEFAULT_INCLUDE_IN_META_KEYWORDS,
            Configuration::DEFAULT_INCLUDE_IN_SITEMAP,

            Configuration::DEFAULT_SHOW_SELECTED_OPTIONS_FIRST,
            Configuration::DEFAULT_SORT_OPTIONS_BY,

            Configuration::DEFAULT_SHOW_MORE_METHOD,
            Configuration::DEFAULT_SHOW_MORE_NUMBER_OF_ITEMS_VISIBLE,
        ];
    }

    protected function getIndexedFields($changes) {
        $db = $this->getConnection();
        
        if (empty($changes['load_defaults'])) {
            $position = "COALESCE(`fge`.`position`, `ca`.`position`)";

            return [
                'edit_id' => new Zend_Db_Expr("`fge`.`id`"),
                'store_id' => new Zend_Db_Expr("0"),
                'is_deleted' => new Zend_Db_Expr("0"),
                'attribute_id' => new Zend_Db_Expr("`a`.`attribute_id`"),
                'attribute_code' => new Zend_Db_Expr("`a`.`attribute_code`"),
                'unique_key' => new Zend_Db_Expr("CONCAT('attribute-', `a`.`attribute_id`)"),
                'param_name' => new Zend_Db_Expr("COALESCE(`fge`.`param_name`, `a`.`attribute_code`)"),
    
                'title' => new Zend_Db_Expr("COALESCE(`fge`.`title`, `a`.`frontend_label`)"),
                'position' => new Zend_Db_Expr($position),
                'show_in_main_sidebar' => new Zend_Db_Expr($db->quoteInto("COALESCE(`fge`.`show_in_main_sidebar`, ?)", $this->configuration->getDefaultShowInMainSidebar() ? 1 : 0)),
                'show_in_additional_sidebar' => new Zend_Db_Expr($db->quoteInto("COALESCE(`fge`.`show_in_additional_sidebar`, ?)", $this->configuration->getDefaultShowInAdditionalSidebar() ? 1 : 0)),
                'show_above_products' => new Zend_Db_Expr($db->quoteInto("COALESCE(`fge`.`show_above_products`, ?)", $this->configuration->getDefaultShowAboveProducts() ? 1 : 0)),
                'show_on_mobile' => new Zend_Db_Expr($db->quoteInto("COALESCE(`fge`.`show_on_mobile`, ?)", $this->configuration->getDefaultShowOnMobile() ? 1 : 0)),
                'is_enabled_in_categories' => new Zend_Db_Expr("COALESCE(`fge`.`is_enabled_in_categories`, 1)"),
                'is_enabled_in_search' => new Zend_Db_Expr("COALESCE(`fge`.`is_enabled_in_search`, `ca`.`is_filterable_in_search`)"),
                'minimum_product_count_per_option' => new Zend_Db_Expr("COALESCE(`fge`.`minimum_product_count_per_option`, IF(`ca`.`is_filterable` = 1, 1, 0))"),

                'calculate_slider_min_max_based_on' => new Zend_Db_Expr($db->quoteInto("COALESCE(`fge`.`calculate_slider_min_max_based_on`, ?)", $this->configuration->getCalculateSliderMinMaxBasedOn())),
                'number_format' => new Zend_Db_Expr($db->quoteInto("COALESCE(`fge`.`number_format`, ?)", "$0")),
                'decimal_digits' => new Zend_Db_Expr($db->quoteInto("COALESCE(`fge`.`decimal_digits`, ?)", "0")),
                'is_two_number_formats' => new Zend_Db_Expr($db->quoteInto("COALESCE(`fge`.`is_two_number_formats`, ?)", "0")),
                'use_second_number_format_on' => new Zend_Db_Expr($db->quoteInto("COALESCE(`fge`.`use_second_number_format_on`, ?)", "0")),
                'second_number_format' => new Zend_Db_Expr($db->quoteInto("COALESCE(`fge`.`second_number_format`, ?)", "0")),
                'second_decimal_digits' => new Zend_Db_Expr($db->quoteInto("COALESCE(`fge`.`second_decimal_digits`, ?)", "0")),
                'show_thousand_separator' => new Zend_Db_Expr($db->quoteInto("COALESCE(`fge`.`show_thousand_separator`, ?)", "0")),
                'is_slide_on_existing_values' => new Zend_Db_Expr($db->quoteInto("COALESCE(`fge`.`is_slide_on_existing_values`, ?)", "0")),
                'is_manual_range' => new Zend_Db_Expr($db->quoteInto("COALESCE(`fge`.`is_manual_range`, ?)", "0")),
                'slider_style' => new Zend_Db_Expr($db->quoteInto("COALESCE(`fge`.`slider_style`, ?)", $this->configuration->getSliderStyle())),
                'min_max_role' => new Zend_Db_Expr($db->quoteInto("COALESCE(`fge`.`min_max_role`, ?)", "min")),
                'min_slider_code' => new Zend_Db_Expr($db->quoteInto("COALESCE(`fge`.`min_slider_code`, ?)", "")),
                'hide_filter_with_single_visible_item' => new Zend_Db_Expr($db->quoteInto("COALESCE(`fge`.`hide_filter_with_single_visible_item`, ?)", $this->configuration->hideFiltersWithSingleVisibleItem() ? 1 : 0)),

                'use_filter_title_in_url' => new Zend_Db_Expr($db->quoteInto("COALESCE(`fge`.`use_filter_title_in_url`, ?)", $this->configuration->getDefaultUseFilterTitleInUrl() ? 1 : 0)),
                'url_part' => new Zend_Db_Expr($db->quoteInto("COALESCE(`fge`.`url_part`, ?)", $this->configuration->getDefaultUrlPart())),
                'position_in_url' => new Zend_Db_Expr("COALESCE(`fge`.`position_in_url`, $position)"),
                'include_in_canonical_url' => new Zend_Db_Expr($db->quoteInto("COALESCE(`fge`.`include_in_canonical_url`, ?)", $this->configuration->getDefaultIncludeInCanonicalUrl() ? 1 : 0)),
                'force_no_index' => new Zend_Db_Expr($db->quoteInto("COALESCE(`fge`.`force_no_index`, ?)", $this->configuration->getDefaultForceNoIndex() ? 1 : 0)),
                'force_no_follow' => new Zend_Db_Expr($db->quoteInto("COALESCE(`fge`.`force_no_follow`, ?)", $this->configuration->getDefaultForceNoFollow() ? 1 : 0)),
                'include_in_meta_title' => new Zend_Db_Expr($db->quoteInto("COALESCE(`fge`.`include_in_meta_title`, ?)", $this->configuration->getDefaultIncludeInMetaTitle() ? 1 : 0)),
                'include_in_meta_description' => new Zend_Db_Expr($db->quoteInto("COALESCE(`fge`.`include_in_meta_description`, ?)", $this->configuration->getDefaultIncludeInMetaDescription() ? 1 : 0)),
                'include_in_meta_keywords' => new Zend_Db_Expr($db->quoteInto("COALESCE(`fge`.`include_in_meta_keywords`, ?)", $this->configuration->getDefaultIncludeInMetaKeywords() ? 1 : 0)),
                'include_in_sitemap' => new Zend_Db_Expr($db->quoteInto("COALESCE(`fge`.`include_in_sitemap`, ?)", $this->configuration->getDefaultIncludeInSitemap() ? 1 : 0)),

                'show_selected_options_first' => new Zend_Db_Expr($db->quoteInto("COALESCE(`fge`.`show_selected_options_first`, ?)", $this->configuration->getDefaultShowSelectedOptionsFirst() ? 1 : 0)),
                'sort_options_by' => new Zend_Db_Expr($db->quoteInto("COALESCE(`fge`.`sort_options_by`, ?)", $this->configuration->getDefaultSortOptionsBy())),

                'show_more_method' => new Zend_Db_Expr($db->quoteInto("COALESCE(`fge`.`show_more_method`, ?)", $this->configuration->getDefaultShowMoreMethod())),
                'show_more_item_limit' => new Zend_Db_Expr($db->quoteInto("COALESCE(`fge`.`show_more_item_limit`, ?)", $this->configuration->getDefaultNumberOfItemsVisible())),
                'show_option_search' => new Zend_Db_Expr($db->quoteInto("COALESCE(`fge`.`show_option_search`, ?)", $this->configuration->getDefaultShowOptionSearch() ? 1 : 0)),

                'use_logical_and' => new Zend_Db_Expr("COALESCE(`fge`.`use_logical_and`, 0)"),
            ];
        }
        else {
            $position = "`ca`.`position`";

            return [
                'edit_id' => new Zend_Db_Expr("NULL"),
                'store_id' => new Zend_Db_Expr("0"),
                'is_deleted' => new Zend_Db_Expr("0"),
                'attribute_id' => new Zend_Db_Expr("`a`.`attribute_id`"),
                'attribute_code' => new Zend_Db_Expr("`a`.`attribute_code`"),
                'unique_key' => new Zend_Db_Expr("CONCAT('attribute-', `a`.`attribute_id`)"),
                'param_name' => new Zend_Db_Expr("`a`.`attribute_code`"),
    
                'title' => new Zend_Db_Expr("`a`.`frontend_label`"),
                'position' => new Zend_Db_Expr($position),
                'show_in_main_sidebar' => new Zend_Db_Expr($db->quoteInto("?", $this->configuration->getDefaultShowInMainSidebar() ? 1 : 0)),
                'show_in_additional_sidebar' => new Zend_Db_Expr($db->quoteInto("?", $this->configuration->getDefaultShowInAdditionalSidebar() ? 1 : 0)),
                'show_above_products' => new Zend_Db_Expr($db->quoteInto("?", $this->configuration->getDefaultShowAboveProducts() ? 1 : 0)),
                'show_on_mobile' => new Zend_Db_Expr($db->quoteInto("?", $this->configuration->getDefaultShowOnMobile() ? 1 : 0)),
                'is_enabled_in_categories' => new Zend_Db_Expr("1"),
                'is_enabled_in_search' => new Zend_Db_Expr("`ca`.`is_filterable_in_search`"),
                'minimum_product_count_per_option' => new Zend_Db_Expr("IF(`ca`.`is_filterable` = 1, 1, 0)"),

                'calculate_slider_min_max_based_on' => new Zend_Db_Expr($db->quoteInto("?", $this->configuration->getCalculateSliderMinMaxBasedOn())),
                'number_format' => new Zend_Db_Expr($db->quoteInto("?", "$0")),
                'decimal_digits' => new Zend_Db_Expr($db->quoteInto("?", "0")),
                'is_two_number_formats' => new Zend_Db_Expr($db->quoteInto("?", "0")),
                'use_second_number_format_on' => new Zend_Db_Expr($db->quoteInto("?", "0")),
                'second_number_format' => new Zend_Db_Expr($db->quoteInto("?", "0")),
                'second_decimal_digits' => new Zend_Db_Expr($db->quoteInto("?", "0")),
                'show_thousand_separator' => new Zend_Db_Expr($db->quoteInto("?", "0")),
                'is_slide_on_existing_values' => new Zend_Db_Expr($db->quoteInto("?", "0")),
                'is_manual_range' => new Zend_Db_Expr($db->quoteInto("?", "0")),
                'slider_style' => new Zend_Db_Expr($db->quoteInto("?", $this->configuration->getSliderStyle())),
                'min_max_role' => new Zend_Db_Expr($db->quoteInto("?", "min")),
                'min_slider_code' => new Zend_Db_Expr($db->quoteInto("?", "")),
                'hide_filter_with_single_visible_item' => new Zend_Db_Expr($db->quoteInto("?", $this->configuration->hideFiltersWithSingleVisibleItem() ? 1 : 0)),

                'use_filter_title_in_url' => new Zend_Db_Expr($db->quoteInto("?", $this->configuration->getDefaultUseFilterTitleInUrl() ? 1 : 0)),
                'url_part' => new Zend_Db_Expr($db->quoteInto("?", $this->configuration->getDefaultUrlPart())),
                'position_in_url' => new Zend_Db_Expr("$position"),
                'include_in_canonical_url' => new Zend_Db_Expr($db->quoteInto("?", $this->configuration->getDefaultIncludeInCanonicalUrl() ? 1 : 0)),
                'force_no_index' => new Zend_Db_Expr($db->quoteInto("?", $this->configuration->getDefaultForceNoIndex() ? 1 : 0)),
                'force_no_follow' => new Zend_Db_Expr($db->quoteInto("?", $this->configuration->getDefaultForceNoFollow() ? 1 : 0)),
                'include_in_meta_title' => new Zend_Db_Expr($db->quoteInto("?", $this->configuration->getDefaultIncludeInMetaTitle() ? 1 : 0)),
                'include_in_meta_description' => new Zend_Db_Expr($db->quoteInto("?", $this->configuration->getDefaultIncludeInMetaDescription() ? 1 : 0)),
                'include_in_meta_keywords' => new Zend_Db_Expr($db->quoteInto("?", $this->configuration->getDefaultIncludeInMetaKeywords() ? 1 : 0)),
                'include_in_sitemap' => new Zend_Db_Expr($db->quoteInto("?", $this->configuration->getDefaultIncludeInSitemap() ? 1 : 0)),

                'show_selected_options_first' => new Zend_Db_Expr($db->quoteInto("?", $this->configuration->getDefaultShowSelectedOptionsFirst() ? 1 : 0)),
                'sort_options_by' => new Zend_Db_Expr($db->quoteInto("?", $this->configuration->getDefaultSortOptionsBy())),

                'show_more_method' => new Zend_Db_Expr($db->quoteInto("?", $this->configuration->getDefaultShowMoreMethod())),
                'show_more_item_limit' => new Zend_Db_Expr($db->quoteInto("?", $this->configuration->getDefaultNumberOfItemsVisible())),
                'show_option_search' => new Zend_Db_Expr($db->quoteInto("?", $this->configuration->getDefaultShowOptionSearch() ? 1 : 0)),

                'use_logical_and' => new Zend_Db_Expr('0'),
            ];
        }
    }

    /**
     * Inserts or updates records in `mana_filter` table on global level
     * @param array $changes
     * @return \Magento\Framework\DB\Select
     */
    public function index($changes = ['all']) {
        $db = $this->getConnection();

        $fields = $this->getIndexedFields($changes);

        $select = $this->select($fields);

        if ($whereClause = $this->scope->limitAttributeIndexing($changes, $fields)) {
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

    protected function select($fields) {
        $db = $this->getConnection();

        return $db->select()
            ->distinct()
            ->from(['a' => $this->getTable('eav_attribute')], null)
            ->join(['ca' => $this->getTable('catalog_eav_attribute')],
                "`ca`.`attribute_id` = `a`.`attribute_id` AND `ca`.`is_filterable` <> 0", null)
            ->join(['et' => $this->getTable('eav_entity_type')],
                $db->quoteInto("`et`.`entity_type_id` = `a`.`entity_type_id`
                    AND `et`.`entity_type_code` = ?", Product::ENTITY), null)
            ->joinLeft(['fge' => $this->getTable('mana_filter_edit')],
                "`fge`.`attribute_id` = `a`.`attribute_id` AND `fge`.`store_id` = 0", null)
            ->columns($fields);
    }
}