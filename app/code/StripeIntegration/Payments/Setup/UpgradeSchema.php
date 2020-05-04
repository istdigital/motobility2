<?php

namespace StripeIntegration\Payments\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use StripeIntegration\Payments\Helper\Logger;
use StripeIntegration\Payments\Model\PaymentMethod;
use StripeIntegration\Payments\Model\Config;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
    }

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.5.2') < 0)
        {
            $this->createWebhooksTable($setup);
        }

        if (version_compare($context->getVersion(), '1.8.0') < 0)
        {
            $this->createSourcesTable($setup);
        }

        $setup->endSetup();
    }

    public function createWebhooksTable($setup)
    {
        $table = $setup->getConnection()->newTable(
                $setup->getTable('stripe_webhooks')
            )->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )->addColumn(
                'config_version',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => \StripeIntegration\Payments\Helper\WebhooksSetup::VERSION],
                'Webhooks Configuration Version'
            )->addColumn(
                'webhook_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Webhook ID'
            )->addColumn(
                'publishable_key',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Stripe API Publishable Key'
            )->addColumn(
                'store_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Store Code'
            )->addColumn(
                'live_mode',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => 0],
                'Live Mode'
            )->addColumn(
                'active',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => 0],
                'Active'
            )->addColumn(
                'last_event',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'unsigned' => true, 'default' => 0],
                'Timestamp of last received event'
            )->addColumn(
                'api_version',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Stripe API Version'
            )->addColumn(
                'url',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                2000,
                ['nullable' => true],
                'Webhook URL'
            )->addColumn(
                'api_version',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Stripe API Version'
            )->addColumn(
                'enabled_events',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                10000,
                ['nullable' => true],
                'Enabled Webhook Events'
            )->addColumn(
                'connect',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => 0],
                'Connected Accounts'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            );
        $setup->getConnection()->createTable($table);
    }

    public function createSourcesTable($setup)
    {
        $table = $setup->getConnection()->newTable(
                $setup->getTable('stripe_sources')
            )->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )->addColumn(
                'source_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Source ID'
            )->addColumn(
                'order_increment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null],
                'Order Increment ID'
            )->addColumn(
                'stripe_customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null],
                'Stripe Customer ID'
            )->addIndex(
                $setup->getIdxName('stripe_sources', ['source_id']),
                ['source_id']
            )->addIndex(
                $setup->getIdxName('stripe_sources', ['order_increment_id']),
                ['order_increment_id']
            )->addIndex(
                $setup->getIdxName('stripe_sources', ['stripe_customer_id']),
                ['stripe_customer_id']
            );

        $setup->getConnection()->createTable($table);
    }
}
