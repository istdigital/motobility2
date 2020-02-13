<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Zemez\Contact\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * UpgradeSchema mock class
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.2' , '<')) {
            /**
            * Create table 'contacts'
            */
            $table = $setup->getConnection()
            ->newTable($setup->getTable('contacts'))
            ->addColumn(
              'id',
              \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
              null,
              ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
              'Increment ID'
            )
            ->addColumn(
              'name',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              255,
              ['nullable' => false, 'default' => ''],
                'Name'
            )->addColumn(
              'email',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              255,
              ['nullable' => false, 'default' => ''],
                'Email'
            )->addColumn(
              'phone',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              255,
              ['nullable' => false, 'default' => ''],
                'Phone'
            )->addColumn(
              'message',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              500,
              ['nullable' => false, 'default' => ''],
                'Message'
            )->addColumn(
              'date',
              \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
              null,
              ['nullable' => false],
                'Date'
            )->setComment("Contacts Entries");
            $setup->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
