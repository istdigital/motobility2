<?php

namespace TemplateMonster\SocialLogin\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $table = $installer->getConnection()->newTable(
            $installer->getTable('social_login_provider')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        )->addColumn(
            'customer_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'Customer Entity Id'
        )->addColumn(
            'provider_code',
            Table::TYPE_TEXT,
            50,
            [],
            'Provider Code'
        )->addColumn(
            'provider_id',
            Table::TYPE_TEXT,
            100,
            [],
            'Provider Id'
        )->addColumn(
            'created_at',
            Table::TYPE_DATETIME,
            null,
            [],
            'Created At'
        )->addIndex(
            $installer->getIdxName(
                'social_login_provider',
                ['provider_code', 'provider_id'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['provider_code', 'provider_id'],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )
        ->addForeignKey(
            $installer->getFkName(
                'social_login_provider',
                'customer_id',
                'customer_entity',
                'entity_id'
            ),
            'customer_id',
            $installer->getTable('customer_entity'),
            'entity_id',
            Table::ACTION_CASCADE
        );
        $installer->getConnection()->createTable($table);
    }
}
