<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\CatalogImagesGrid\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Catalog\Model\Category;

/**
 * Class UpgradeData
 *
 * @package TemplateMonster\CatalogImagesGrid\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * Category setup factory
     *
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * Init
     *
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(CategorySetupFactory $categorySetupFactory)
    {
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * @inheritdoc
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        /** @var \Magento\Catalog\Setup\CategorySetup $categorySetup */
        $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $groupName = 'Catalog Images Grid';
            $attributeCodes = ['thumbnail', 'grid_activate', 'icon_class'];
            if (!$categorySetup->getAttributeGroup(Category::ENTITY, 'Default', $groupName)) {
                $categorySetup->addAttributeGroup(Category::ENTITY, 'Default', $groupName, 60);
            }
            foreach ($attributeCodes as $code) {
                $categorySetup->addAttributeToGroup(Category::ENTITY, 'Default', 'Catalog Images Grid', $code);
            }
        }

        $setup->endSetup();
    }
}