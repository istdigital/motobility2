<?php
/**
* Copyright © 2016 Magento. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Zemez\StockAlert\Setup;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
    * {@inheritdoc}
    * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
    */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
          /**
          * Create table 'greeting_message'
          */
          $table = $setup->getConnection()
              ->newTable($setup->getTable('stockalert_subscriber'))
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
                  'product_name',
                  \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                  255,
                  ['nullable' => false, 'default' => ''],
                    'Product Name'
              )->addColumn(
                  'product_sku',
                  \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                  255,
                  ['nullable' => false, 'default' => ''],
                    'Product SKU'
              )->addColumn(
                  'product_id',
                  \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                  null,
                  ['nullable' => false, 'unsigned' => true],
                    'Product ID'
              )->addColumn(
                  'date',
                  \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                  null,
                  ['nullable' => false],
                    'Date'
              )->addColumn(
                  'notified_at',
                  \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                  null,
                  ['nullable' => true],
                    'Date'
              )->addColumn(
                  'status',
                  \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                  100,
                  ['nullable' => false, 'default' => 'to_be_notified'],
                    'Status'
              )->addIndex(
                  $setup->getIdxName(
                      'stockalert_subscriber',
                      ['email', 'product_id'],
                      \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                  ),
                  ['email', 'product_id'],
                  ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
              )->setComment("Stock Alert Subscriber Listing table");
          $setup->getConnection()->createTable($table);
      }
}
