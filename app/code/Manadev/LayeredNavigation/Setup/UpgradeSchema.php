<?php
/** 
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface {

    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        if (version_compare($context->getVersion(), '2') < 0) {
            $setup->startSetup();
            $db = $setup->getConnection();

            $tableName = 'mana_filter';
            $db->addColumn($setup->getTable($tableName), 'type', ['type' => Table::TYPE_TEXT, 'length' => 20, 'nullable' => false, 'comment' => '..']);

            $setup->endSetup();
        }
        if (version_compare($context->getVersion(), '3') < 0) {
            $setup->startSetup();
            $db = $setup->getConnection();

            $tableName = 'mana_filter_edit';
            $db->addColumn($setup->getTable($tableName), 'show_in', ['type' => Table::TYPE_TEXT, 'length' => 255, 'nullable' => true, 'comment' => '..']);

            $tableName = 'mana_filter';
            $db->addColumn($setup->getTable($tableName), 'show_in', ['type' => Table::TYPE_TEXT, 'length' => 255, 'nullable' => false, 'comment' => '..']);

            $setup->endSetup();
        }
        if (version_compare($context->getVersion(), '4') < 0) {
            $setup->startSetup();
            $db = $setup->getConnection();

            $tableName = 'mana_filter_edit';
            $db->addColumn($setup->getTable($tableName), 'minimum_product_count_per_option', ['type' => Table::TYPE_INTEGER, 'default' => '1', 'nullable' => true, 'comment' => '..']);

            $tableName = 'mana_filter';
            $db->addColumn($setup->getTable($tableName), 'minimum_product_count_per_option', ['type' => Table::TYPE_INTEGER, 'default' => '1', 'nullable' => false, 'comment' => '..']);

            $setup->endSetup();
        }
        if (version_compare($context->getVersion(), '5') < 0) {
            $setup->startSetup();
            $db = $setup->getConnection();

            $tableName = 'mana_filter_edit';
            $db->addColumn($setup->getTable($tableName), 'is_enabled_in_categories', ['type' => Table::TYPE_BOOLEAN, 'nullable' => true, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'is_enabled_in_search', ['type' => Table::TYPE_BOOLEAN, 'nullable' => true, 'comment' => '..']);

            $tableName = 'mana_filter';
            $db->addColumn($setup->getTable($tableName), 'is_enabled_in_categories', ['type' => Table::TYPE_BOOLEAN, 'default' => '1', 'nullable' => false, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'is_enabled_in_search', ['type' => Table::TYPE_BOOLEAN, 'default' => '1', 'nullable' => false, 'comment' => '..']);

            $setup->endSetup();
        }
        if (version_compare($context->getVersion(), '6') < 0) {
            $setup->startSetup();
            $db = $setup->getConnection();

            $tableName = 'mana_filter';
            $db->addColumn($setup->getTable($tableName), 'attribute_code', ['type' => Table::TYPE_TEXT, 'length' => 255, 'nullable' => true, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'swatch_input_type', ['type' => Table::TYPE_TEXT, 'length' => 50, 'nullable' => true, 'comment' => '..']);

            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), '7') < 0) {
            $setup->startSetup();
            $db = $setup->getConnection();

            $tableName = 'mana_filter_edit';
            $db->dropColumn($setup->getTable($tableName), 'data_source');

            $tableName = 'mana_filter';
            $db->dropColumn($setup->getTable($tableName), 'data_source');

            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), '8') < 0) {
            $setup->startSetup();
            $db = $setup->getConnection();

            $tableName = 'mana_filter_edit';
            $db->addColumn($setup->getTable($tableName), 'type', ['type' => Table::TYPE_TEXT, 'length' => 20, 'nullable' => false, 'comment' => '..']);
            $db->addIndex($setup->getTable($tableName), $setup->getIdxName($tableName, ['type']), ['type']);

            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), '9') < 0) {
            $setup->startSetup();
            $db = $setup->getConnection();

            $tableName = 'mana_filter_edit';
            $db->addColumn($setup->getTable($tableName), 'param_name', ['type' => Table::TYPE_TEXT, 'length' => 255, 'nullable' => true, 'comment' => '..']);
            $db->modifyColumn($setup->getTable($tableName), 'filter_id', ['type' => Table::TYPE_BIGINT, 'nullable' => true, 'comment' => '..']);

            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), '10') < 0) {
            $setup->startSetup();
            $db = $setup->getConnection();

            $tableName = 'mana_filter_edit';
            $db->modifyColumn($setup->getTable($tableName), 'filter_id', ['type' => Table::TYPE_BIGINT, 'nullable' => false, 'comment' => '..']);
            
            $setup->endSetup();
        }
        if (version_compare($context->getVersion(), '11') < 0) {
            $setup->startSetup();
            $db = $setup->getConnection();

            $tableName = 'mana_filter_edit';
            $db->addColumn($setup->getTable($tableName), 'show_in_left_column', ['type' => Table::TYPE_BOOLEAN, 'nullable' => true, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'show_in_right_column', ['type' => Table::TYPE_BOOLEAN, 'nullable' => true, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'show_above_products', ['type' => Table::TYPE_BOOLEAN, 'nullable' => true, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'show_on_mobile', ['type' => Table::TYPE_BOOLEAN, 'nullable' => true, 'comment' => '..']);
            $db->dropColumn($setup->getTable($tableName), 'show_in');

            $tableName = 'mana_filter';
            $db->addColumn($setup->getTable($tableName), 'show_in_left_column', ['type' => Table::TYPE_BOOLEAN, 'default' => '1', 'nullable' => false, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'show_in_right_column', ['type' => Table::TYPE_BOOLEAN, 'default' => '0', 'nullable' => false, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'show_above_products', ['type' => Table::TYPE_BOOLEAN, 'default' => '0', 'nullable' => false, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'show_on_mobile', ['type' => Table::TYPE_BOOLEAN, 'default' => '0', 'nullable' => false, 'comment' => '..']);
            $db->dropColumn($setup->getTable($tableName), 'show_in');

            $setup->endSetup();
        }
        if (version_compare($context->getVersion(), '12') < 0) {
            $setup->startSetup();
            $db = $setup->getConnection();

            $tableName = 'mana_filter_edit';
            $db->dropColumn($setup->getTable($tableName), 'show_in_left_column');
            $db->dropColumn($setup->getTable($tableName), 'show_in_right_column');
            $db->addColumn($setup->getTable($tableName), 'show_in_main_sidebar', ['type' => Table::TYPE_BOOLEAN, 'nullable' => true, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'show_in_additional_sidebar', ['type' => Table::TYPE_BOOLEAN, 'nullable' => true, 'comment' => '..']);

            $tableName = 'mana_filter';
            $db->dropColumn($setup->getTable($tableName), 'show_in_left_column');
            $db->dropColumn($setup->getTable($tableName), 'show_in_right_column');
            $db->addColumn($setup->getTable($tableName), 'show_in_main_sidebar', ['type' => Table::TYPE_BOOLEAN, 'default' => '1', 'nullable' => false, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'show_in_additional_sidebar', ['type' => Table::TYPE_BOOLEAN, 'default' => '0', 'nullable' => false, 'comment' => '..']);

            $setup->endSetup();
        }
        if (version_compare($context->getVersion(), '13') < 0) {
            $setup->startSetup();
            $db = $setup->getConnection();

            $tableName = 'mana_filter_edit';
            $db->addColumn($setup->getTable($tableName), 'calculate_slider_min_max_based_on', ['type' => Table::TYPE_TEXT, 'length' => 20, 'nullable' => true, 'comment' => '..']);

            $tableName = 'mana_filter';
            $db->addColumn($setup->getTable($tableName), 'calculate_slider_min_max_based_on', ['type' => Table::TYPE_TEXT, 'length' => 20, 'default' => 'page', 'nullable' => false, 'comment' => '..']);

            $setup->endSetup();
        }
        if (version_compare($context->getVersion(), '14') < 0) {
            $setup->startSetup();
            $db = $setup->getConnection();

            $tableName = 'mana_filter_edit';
            $db->addColumn($setup->getTable($tableName), 'number_format', ['type' => Table::TYPE_TEXT, 'length' => 20, 'nullable' => true, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'decimal_digits', ['type' => Table::TYPE_INTEGER, 'default' => '0', 'nullable' => true, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'is_two_number_formats', ['type' => Table::TYPE_BOOLEAN, 'nullable' => true, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'use_second_number_format_on', ['type' => Table::TYPE_INTEGER, 'nullable' => true, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'second_number_format', ['type' => Table::TYPE_TEXT, 'length' => 20, 'nullable' => true, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'second_decimal_digits', ['type' => Table::TYPE_INTEGER, 'nullable' => true, 'comment' => '..']);


            $tableName = 'mana_filter';
            $db->addColumn($setup->getTable($tableName), 'number_format', ['type' => Table::TYPE_TEXT, 'length' => 20, 'default' => '$0', 'nullable' => false, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'decimal_digits', ['type' => Table::TYPE_INTEGER, 'default' => '0', 'nullable' => false, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'is_two_number_formats', ['type' => Table::TYPE_BOOLEAN, 'default' => '0', 'nullable' => false, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'use_second_number_format_on', ['type' => Table::TYPE_INTEGER, 'default' => '0', 'nullable' => false, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'second_number_format', ['type' => Table::TYPE_TEXT, 'length' => 20, 'default' => '0', 'nullable' => false, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'second_decimal_digits', ['type' => Table::TYPE_INTEGER, 'default' => '0', 'nullable' => false, 'comment' => '..']);

            $setup->endSetup();
        }
        if (version_compare($context->getVersion(), '15') < 0) {
            $setup->startSetup();
            $db = $setup->getConnection();

            $tableName = 'mana_filter_edit';
            $db->addColumn($setup->getTable($tableName), 'show_thousand_separator', ['type' => Table::TYPE_BOOLEAN, 'nullable' => true, 'comment' => '..']);


            $tableName = 'mana_filter';
            $db->addColumn($setup->getTable($tableName), 'show_thousand_separator', ['type' => Table::TYPE_BOOLEAN, 'default' => '0', 'nullable' => false, 'comment' => '..']);

            $setup->endSetup();
        }
        if (version_compare($context->getVersion(), '16') < 0) {
            $setup->startSetup();
            $db = $setup->getConnection();

            $tableName = 'mana_filter_edit';
            $db->addColumn($setup->getTable($tableName), 'is_slide_on_existing_values', ['type' => Table::TYPE_BOOLEAN, 'nullable' => true, 'comment' => '..']);


            $tableName = 'mana_filter';
            $db->addColumn($setup->getTable($tableName), 'is_slide_on_existing_values', ['type' => Table::TYPE_BOOLEAN, 'default' => '0', 'nullable' => false, 'comment' => '..']);

            $setup->endSetup();
        }
        if (version_compare($context->getVersion(), '17') < 0) {
            $setup->startSetup();
            $db = $setup->getConnection();

            $tableName = 'mana_filter_edit';
            $db->addColumn($setup->getTable($tableName), 'is_manual_range', ['type' => Table::TYPE_BOOLEAN, 'nullable' => true, 'comment' => '..']);


            $tableName = 'mana_filter';
            $db->addColumn($setup->getTable($tableName), 'is_manual_range', ['type' => Table::TYPE_BOOLEAN, 'default' => '0', 'nullable' => false, 'comment' => '..']);

            $setup->endSetup();
        }
        if (version_compare($context->getVersion(), '18') < 0) {
            $setup->startSetup();
            $db = $setup->getConnection();

            $tableName = 'mana_filter_edit';
            $db->addColumn($setup->getTable($tableName), 'slider_style', ['type' => Table::TYPE_TEXT, 'length' => 20, 'nullable' => true, 'comment' => '..']);

            $tableName = 'mana_filter';
            $db->addColumn($setup->getTable($tableName), 'slider_style', ['type' => Table::TYPE_TEXT, 'length' => 20, 'default' => '', 'nullable' => false, 'comment' => '..']);

            $setup->endSetup();
        }
        if (version_compare($context->getVersion(), '19') < 0) {
            $setup->startSetup();
            $db = $setup->getConnection();

            $tableName = 'mana_filter_edit';
            $db->addColumn($setup->getTable($tableName), 'min_max_role', ['type' => Table::TYPE_TEXT, 'length' => 20, 'default' => '', 'nullable' => true, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'min_slider_code', ['type' => Table::TYPE_TEXT, 'length' => 255, 'default' => '', 'nullable' => true, 'comment' => '..']);

            $tableName = 'mana_filter';
            $db->addColumn($setup->getTable($tableName), 'min_max_role', ['type' => Table::TYPE_TEXT, 'length' => 20, 'nullable' => false, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'min_slider_code', ['type' => Table::TYPE_TEXT, 'length' => 255, 'nullable' => false, 'comment' => '..']);

            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), '20') < 0) {
            $setup->startSetup();
            $db = $setup->getConnection();

            $tableName = 'mana_filter_edit';
            $db->addColumn($setup->getTable($tableName), 'hide_filter_with_single_visible_item', ['type' => Table::TYPE_BOOLEAN, 'nullable' => true, 'comment' => '..']);

            $tableName = 'mana_filter';
            $db->addColumn($setup->getTable($tableName), 'hide_filter_with_single_visible_item', ['type' => Table::TYPE_BOOLEAN, 'default' => '0', 'nullable' => false, 'comment' => '..']);

            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), '22') < 0) {
            $setup->startSetup();
            $db = $setup->getConnection();

            $tableName = 'mana_filter_edit';
            $db->addColumn($setup->getTable($tableName), 'use_filter_title_in_url', ['type' => Table::TYPE_BOOLEAN, 'nullable' => true, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'url_part', ['type' => Table::TYPE_TEXT, 'length' => 20, 'default' => 'suffix', 'nullable' => true, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'position_in_url', ['type' => Table::TYPE_INTEGER, 'default' => '0', 'nullable' => true, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'include_in_canonical_url', ['type' => Table::TYPE_BOOLEAN, 'default' => '0', 'nullable' => true, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'force_no_index', ['type' => Table::TYPE_BOOLEAN, 'default' => '0', 'nullable' => true, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'force_no_follow', ['type' => Table::TYPE_BOOLEAN, 'default' => '0', 'nullable' => true, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'include_in_meta_title', ['type' => Table::TYPE_BOOLEAN, 'default' => '0', 'nullable' => true, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'include_in_meta_description', ['type' => Table::TYPE_BOOLEAN, 'default' => '0', 'nullable' => true, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'include_in_meta_keywords', ['type' => Table::TYPE_BOOLEAN, 'default' => '0', 'nullable' => true, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'include_in_sitemap', ['type' => Table::TYPE_BOOLEAN, 'default' => '0', 'nullable' => true, 'comment' => '..']);

            $tableName = 'mana_filter';
            $db->addColumn($setup->getTable($tableName), 'use_filter_title_in_url', ['type' => Table::TYPE_BOOLEAN, 'default' => '0', 'nullable' => false, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'url_part', ['type' => Table::TYPE_TEXT, 'length' => 20, 'default' => 'suffix', 'nullable' => false, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'position_in_url', ['type' => Table::TYPE_INTEGER, 'default' => '0', 'nullable' => false, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'include_in_canonical_url', ['type' => Table::TYPE_BOOLEAN, 'default' => '0', 'nullable' => false, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'force_no_index', ['type' => Table::TYPE_BOOLEAN, 'default' => '0', 'nullable' => false, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'force_no_follow', ['type' => Table::TYPE_BOOLEAN, 'default' => '0', 'nullable' => false, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'include_in_meta_title', ['type' => Table::TYPE_BOOLEAN, 'default' => '0', 'nullable' => false, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'include_in_meta_description', ['type' => Table::TYPE_BOOLEAN, 'default' => '0', 'nullable' => false, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'include_in_meta_keywords', ['type' => Table::TYPE_BOOLEAN, 'default' => '0', 'nullable' => false, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'include_in_sitemap', ['type' => Table::TYPE_BOOLEAN, 'default' => '0', 'nullable' => false, 'comment' => '..']);

            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), '23') < 0) {
            $setup->startSetup();
            $db = $setup->getConnection();

            $tableName = 'cms_page';
            $db->addColumn($setup->getTable($tableName), 'mana_add_layered_navigation_and_products', ['type' => Table::TYPE_BOOLEAN, 'default' => '0', 'nullable' => false, 'comment' => '..']);

            $setup->endSetup();

        }

        if (version_compare($context->getVersion(), '24') < 0) {
            $setup->startSetup();
            $db = $setup->getConnection();

            $tableName = 'mana_filter_edit';
            $db->addColumn($setup->getTable($tableName), 'show_selected_options_first', ['type' => Table::TYPE_BOOLEAN, 'nullable' => true, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'sort_options_by', ['type' => Table::TYPE_TEXT, 'length' => 40, 'default' => 'position', 'nullable' => true, 'comment' => '..']);

            $tableName = 'mana_filter';
            $db->addColumn($setup->getTable($tableName), 'show_selected_options_first', ['type' => Table::TYPE_BOOLEAN, 'default' => '0', 'nullable' => false, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'sort_options_by', ['type' => Table::TYPE_TEXT, 'length' => 40, 'default' => 'position', 'nullable' => false, 'comment' => '..']);

            $setup->endSetup();

        }
        if (version_compare($context->getVersion(), '25') < 0) {
            $setup->startSetup();
            $db = $setup->getConnection();

            $tableName = 'mana_filter_edit';
            $db->addColumn($setup->getTable($tableName), 'show_more_method', ['type' => Table::TYPE_TEXT, 'length' => 255, 'nullable' => true, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'show_more_item_limit', ['type' => Table::TYPE_INTEGER, 'nullable' => true, 'comment' => '..']);

            $tableName = 'mana_filter';
            $db->addColumn($setup->getTable($tableName), 'show_more_method', ['type' => Table::TYPE_TEXT, 'length' => 255, 'nullable' => false, 'comment' => '..']);
            $db->addColumn($setup->getTable($tableName), 'show_more_item_limit', ['type' => Table::TYPE_INTEGER, 'nullable' => false, 'comment' => '..']);

            $setup->endSetup();
        }
        if (version_compare($context->getVersion(), '26') < 0) {
            $setup->startSetup();
            $db = $setup->getConnection();

            $tableName = 'mana_filter_edit';
            $db->addColumn($setup->getTable($tableName), 'show_option_search', ['type' => Table::TYPE_BOOLEAN, 'nullable' => true, 'comment' => '..']);

            $tableName = 'mana_filter';
            $db->addColumn($setup->getTable($tableName), 'show_option_search', ['type' => Table::TYPE_BOOLEAN, 'nullable' => false, 'comment' => '..']);

            $setup->endSetup();
        }
        if (version_compare($context->getVersion(), '27') < 0) {
            $setup->startSetup();
            $db = $setup->getConnection();

            $tableName = 'mana_filter_edit';
            $db->addColumn($setup->getTable($tableName), 'use_logical_and', ['type' => Table::TYPE_BOOLEAN, 'nullable' => true, 'comment' => '..']);

            $tableName = 'mana_filter';
            $db->addColumn($setup->getTable($tableName), 'use_logical_and', ['type' => Table::TYPE_BOOLEAN, 'default' => '0', 'nullable' => false, 'comment' => '..']);

            $setup->endSetup();
        }
    }
}