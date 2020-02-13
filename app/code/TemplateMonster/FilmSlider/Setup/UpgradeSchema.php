<?php
namespace TemplateMonster\FilmSlider\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
     public function upgrade(
         SchemaSetupInterface $setup,
         ModuleContextInterface $context
     ){
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.1', '<')) {

            $tableName = $setup->getTable('film_slider_item');
            $setup->getConnection()->addColumn(
                $tableName,
                'sort_item',
                [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'length' => 10,
                ['nullable' => true, 'default' => null],
                'comment' => 'Sort order item'
            ]);
        }
        $setup->endSetup();
    }
}