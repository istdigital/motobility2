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

        if (version_compare($context->getVersion(), '1.8.8') < 0)
        {
            $this->createSubscriptionsTable($setup);
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

    public function createSubscriptionsTable($setup)
    {
        $table = $setup->getConnection()->newTable(
                $setup->getTable('stripe_subscriptions')
            )->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 0],
                'Store ID'
            )->addColumn(
                'livemode',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => true],
                'Stripe API Mode'
            )->addColumn(
                'subscription_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Stripe Subscription ID'
            )->addColumn(
                'order_increment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Order Increment ID'
            )->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Product ID'
            )->addColumn(
                'magento_customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => true, 'default' => 0],
                'Magento Customer ID'
            )->addColumn(
                'stripe_customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Stripe Customer ID'
            )->addColumn(
                'payment_method_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Payment Method ID'
            )->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                2048,
                ['nullable' => false],
                'Subscription Name'
            )->addColumn(
                'quantity',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => 1],
                'Subscription Quantity'
            )->addColumn(
                'currency',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Currency Code'
            )->addColumn(
                'grand_total',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '10,4',
                ['unsigned' => false, 'nullable' => false],
                'Grand Total'
            )->addColumn(
                'is_new',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => true],
                'Subscription Just Created?'
            )->addIndex(
                $setup->getIdxName('stripe_subscriptions', ['subscription_id']),
                ['subscription_id']
            )->addIndex(
                $setup->getIdxName('stripe_subscriptions', ['store_id']),
                ['store_id']
            )->addIndex(
                $setup->getIdxName('stripe_subscriptions', ['livemode']),
                ['livemode']
            )->addIndex(
                $setup->getIdxName('stripe_subscriptions', ['stripe_customer_id']),
                ['stripe_customer_id']
            )->addIndex(
                $setup->getIdxName('stripe_subscriptions', ['magento_customer_id']),
                ['magento_customer_id']
            )->addIndex(
                $setup->getIdxName('stripe_subscriptions', ['product_id']),
                ['product_id']
            )->addIndex(
                $setup->getIdxName('stripe_subscriptions', ['created_at']),
                ['created_at']
            )->addIndex(
                $setup->getIdxName('stripe_subscriptions', ['is_new']),
                ['is_new']
            )->addIndex(
                $setup->getIdxName('stripe_subscriptions', ['order_increment_id']),
                ['order_increment_id']
            );

        $setup->getConnection()->createTable($table);
    }
}
