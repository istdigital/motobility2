<?php

namespace StripeIntegration\Payments\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{
    public function uninstall(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        $this->removeAttribute('catalog_product', 'stripe_sub_enabled');
        $this->removeAttribute('catalog_product', 'stripe_sub_interval');
        $this->removeAttribute('catalog_product', 'stripe_sub_interval_count');
        $this->removeAttribute('catalog_product', 'stripe_sub_trial');

        $connection = $setup->getConnection();
        $connection->dropTable($connection->getTableName('stripe_customers'));

        $setup->endSetup();
    }
}
