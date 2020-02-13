<?php
namespace TemplateMonster\FeaturedProduct\Setup;

use \Magento\Eav\Setup\EavSetup;
use \Magento\Eav\Setup\EavSetupFactory;
use \Magento\Framework\Setup\UpgradeDataInterface;
use \Magento\Framework\Setup\ModuleContextInterface;
use \Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if ($context->getVersion()
            && version_compare($context->getVersion(), '1.0.4') < 0
        ) {

            /** @var EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            /**
             * Add attributes to the eav/attribute
             */

            $attributeCode = 'on_hover';
            $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);

            $eavSetup
                ->removeAttribute( \Magento\Catalog\Model\Product::ENTITY, $attributeCode)
                ->addAttribute(
                    \Magento\Catalog\Model\Product::ENTITY,
                    $attributeCode,
                    [
                        'type' => 'varchar',
                        'label' => 'On Hover',
                        'input' => 'media_image',
                        'frontend' => 'Magento\Catalog\Model\Product\Attribute\Frontend\Image',
                        'required' => false,
                        'sort_order' => 10,
                        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                        'used_in_product_listing' => true
                    ]
                );

            $groupId = (int)$eavSetup->getAttributeGroupByCode(
                $entityTypeId,
                'Default',
                'image-management',
                'attribute_group_id'
            );

            $eavSetup->addAttributeToGroup($entityTypeId, 'Default', $groupId, $attributeCode);
        }

        $setup->endSetup();
    }
}