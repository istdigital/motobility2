<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
namespace Manadev\Core\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

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

            $tableName = 'mana_extension';
            $db->addColumn($setup->getTable($tableName), 'available_version', ['type' => Table::TYPE_TEXT, 'length' => 20, 'nullable' => true, 'comment' => '..']);

            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), '3') < 0) {
            $setup->startSetup();
            $db = $setup->getConnection();

            $db->delete($setup->getTable('mana_extension'));

            $setup->endSetup();
        }
    }
}