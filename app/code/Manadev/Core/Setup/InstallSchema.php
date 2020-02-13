<?php
/** 
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

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

        $tableName = 'mana_extension';

        /** @var Table $table */
        $table = $db->newTable($setup->getTable($tableName))
            //
            // ID, foreign keys and infrastructure
            //
            ->addColumn(
                'id',
                Table::TYPE_BIGINT,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn('title', Table::TYPE_TEXT, 255, ['nullable' => false])
            ->addColumn('store_id', Table::TYPE_SMALLINT, null, ['unsigned' => true, 'nullable' => true])
            ->addIndex($setup->getIdxName($tableName, ['store_id']), ['store_id'])
            ->addForeignKey(
                $setup->getFkName($tableName, 'store_id', 'store', 'store_id'),
                'store_id',
                $setup->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE,
                Table::ACTION_CASCADE
            )
            ->addColumn('is_enabled', Table::TYPE_BOOLEAN, ['nullable' => false, 'default' => true])
            ->addColumn('is_pending', Table::TYPE_BOOLEAN, ['nullable' => false, 'default' => false]);
        $db->createTable($table);

        $setup->endSetup();
    }
}