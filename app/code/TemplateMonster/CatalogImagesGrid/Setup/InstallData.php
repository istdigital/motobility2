<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\CatalogImagesGrid\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Catalog\Model\Category;

class InstallData implements InstallDataInterface
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

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var \Magento\Catalog\Setup\CategorySetup $categorySetup */
        $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
        $categorySetup->removeAttribute(Category::ENTITY, "thumbnail");

        $categorySetup->addAttribute(Category::ENTITY, "thumbnail", [
                'type' => 'varchar',
                'label' => 'Thumbnail',
                'input' => 'image',
                'backend' => 'Magento\Catalog\Model\Category\Attribute\Backend\Image',
                'required' => false,
                'sort_order' => 5,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Content',
            ]
        );

        $categorySetup->removeAttribute(Category::ENTITY, "grid_activate");

        $categorySetup->addAttribute(Category::ENTITY, "grid_activate", [
                'type' => 'int',
                'label' => 'Activate Image Grid',
                'input' => 'select',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'sort_order' => 6,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'required' => false,
                'group' => 'Content',
            ]
        );

        $categorySetup->removeAttribute(Category::ENTITY, "icon_class");

        $categorySetup->addAttribute(Category::ENTITY, "icon_class", [
                'type'          => 'varchar',
                'label'         => 'Css class for Font Icon',
                'input'         => 'text',
                'global'        =>  \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'required'      =>  false,
                'group'         =>  'Content',
                'sort_order'    =>  7,
            ]
        );

    }

}