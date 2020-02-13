<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Zemez\Repayment\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * UpgradeSchema mock class
 */
class UpgradeSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
        * Create table 'contacts'
        */
        $table = $setup->getConnection()
        ->newTable($setup->getTable('repayment_enquiry'))
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
          'suburb',
          \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
          255,
          ['nullable' => false, 'default' => ''],
            'Suburb'
        )->addColumn(
          'message',
          \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
          500,
          ['nullable' => false, 'default' => ''],
            'Message'
        )->addColumn(
          'term',
          \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
          50,
          ['nullable' => false, 'default' => ''],
            'Term'
        )->addColumn(
          'product_id',
          \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
          null,
          ['nullable' => false, 'unsigned' => true],
            'Product ID'
        )->addColumn(
          'price',
          \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
          '10,2',
          ['nullable' => false],
            'Price'
        )->addColumn(
          'application_fee',
          \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
          '10,2',
          ['nullable' => false],
            'Application Fee'
        )->addColumn(
          'deposit',
          \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
          '10,2',
          ['nullable' => false],
            'Deposit'
        )->addColumn(
          'sub_total',
          \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
          '10,2',
          ['nullable' => false],
            'Subtotal'
        )->addColumn(
          'payment_charge',
          \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
          '10,2',
          ['nullable' => false],
            'Payment Charge'
        )->addColumn(
          'total',
          \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
          '10,2',
          ['nullable' => false],
            'Total'
        )->addColumn(
          'date',
          \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
          null,
          ['nullable' => false],
            'Date'
        )->setComment("Repayment Enquiry");
        $setup->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
