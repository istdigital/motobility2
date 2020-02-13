<?php
/** 
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface {

    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $setup->startSetup();
        $db = $setup->getConnection();

        $tableName = 'mana_filter_edit';
        $table = $db->newTable($setup->getTable($tableName))

            //
            // ID, foreign keys and infrastructure
            //

            ->addColumn('id', Table::TYPE_BIGINT, null,
                ['identity' => true, 'nullable' => false, 'primary' => true])

            ->addColumn('filter_id', Table::TYPE_BIGINT)
            ->addIndex($setup->getIdxName($tableName, ['filter_id']), ['filter_id'])
            ->addForeignKey($setup->getFkName($tableName, 'filter_id', 'mana_filter', 'id'),
                'filter_id', $setup->getTable('mana_filter'), 'id',
                Table::ACTION_CASCADE, Table::ACTION_CASCADE)

            ->addColumn('store_id', Table::TYPE_SMALLINT, null, ['unsigned' => true, 'nullable' => false])
            ->addIndex($setup->getIdxName($tableName, ['store_id']), ['store_id'])
            ->addForeignKey($setup->getFkName($tableName, 'store_id', 'store', 'store_id'),
                'store_id', $setup->getTable('store'), 'store_id',
                Table::ACTION_CASCADE, Table::ACTION_CASCADE)

            ->addColumn('data_source', Table::TYPE_TEXT, 20, ['nullable' => false])
            ->addIndex($setup->getIdxName($tableName, ['data_source']), ['data_source'])

            ->addColumn('attribute_id', Table::TYPE_SMALLINT, null, ['unsigned' => true])
            ->addIndex($setup->getIdxName($tableName, ['attribute_id']), ['attribute_id'])
            ->addForeignKey($setup->getFkName($tableName, 'attribute_id', 'eav_attribute', 'attribute_id'),
                'attribute_id', $setup->getTable('eav_attribute'), 'attribute_id',
                Table::ACTION_CASCADE, Table::ACTION_CASCADE)

            //
            // general settings
            //

            ->addColumn('title', Table::TYPE_TEXT, 255)
            ->addColumn('position', Table::TYPE_INTEGER)
            ->addColumn('template', Table::TYPE_TEXT, 255)
        ;
        $db->createTable($table);

        $tableName = 'mana_filter';
        $table = $db->newTable($setup->getTable($tableName))

            //
            // ID, foreign keys and infrastructure
            //

            ->addColumn('id', Table::TYPE_BIGINT, null,
                ['identity' => true, 'nullable' => false, 'primary' => true])

            ->addColumn('edit_id', Table::TYPE_BIGINT)
            ->addIndex($setup->getIdxName($tableName, ['edit_id']), ['edit_id'])
            ->addForeignKey($setup->getFkName($tableName, 'edit_id', 'mana_filter_edit', 'id'),
                'edit_id', $setup->getTable('mana_filter_edit'), 'id',
                Table::ACTION_SET_NULL, Table::ACTION_SET_NULL)

            ->addColumn('filter_id', Table::TYPE_BIGINT)
            ->addIndex($setup->getIdxName($tableName, ['filter_id']), ['filter_id'])
            ->addForeignKey($setup->getFkName($tableName, 'filter_id', 'mana_filter', 'id'),
                'filter_id', $setup->getTable('mana_filter'), 'id',
                Table::ACTION_CASCADE, Table::ACTION_CASCADE)

            ->addColumn('store_id', Table::TYPE_SMALLINT, null, ['unsigned' => true, 'nullable' => false])
            ->addIndex($setup->getIdxName($tableName, ['store_id']), ['store_id'])
            ->addForeignKey($setup->getFkName($tableName, 'store_id', 'store', 'store_id'),
                'store_id', $setup->getTable('store'), 'store_id',
                Table::ACTION_CASCADE, Table::ACTION_CASCADE)

            ->addColumn('data_source', Table::TYPE_TEXT, 20, ['nullable' => false])
            ->addIndex($setup->getIdxName($tableName, ['data_source']), ['data_source'])

            ->addColumn('is_deleted', Table::TYPE_BOOLEAN, 20, ['nullable' => false, 'default' => 0])
            ->addIndex($setup->getIdxName($tableName, ['is_deleted']), ['is_deleted'])

            ->addColumn('attribute_id', Table::TYPE_SMALLINT, null, ['unsigned' => true])
            ->addIndex($setup->getIdxName($tableName, ['attribute_id']), ['attribute_id'])
            ->addForeignKey($setup->getFkName($tableName, 'attribute_id', 'eav_attribute', 'attribute_id'),
                'attribute_id', $setup->getTable('eav_attribute'), 'attribute_id',
                Table::ACTION_CASCADE, Table::ACTION_CASCADE)

            ->addColumn('unique_key', Table::TYPE_TEXT, 255, ['nullable' => false])
            ->addIndex($setup->getIdxName($tableName, ['unique_key']), ['unique_key'], ['type' => 'unique'])

            ->addColumn('param_name', Table::TYPE_TEXT, 255, ['nullable' => false])

            //
            // general settings
            //

            ->addColumn('title', Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => ''])
            ->addColumn('position', Table::TYPE_INTEGER, null, ['nullable' => false, 'default' => '0'])
            ->addColumn('template', Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => ''])
        ;
        $db->createTable($table);


        $setup->endSetup();
    }
}