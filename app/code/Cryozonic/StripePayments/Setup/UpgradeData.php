<?php

namespace Cryozonic\StripePayments\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Cryozonic\StripePayments\Helper\Logger;

class UpgradeData implements \Magento\Framework\Setup\UpgradeDataInterface
{
    protected $categorySetupFactory;

    public function __construct(
        \Magento\Catalog\Setup\CategorySetupFactory $categorySetupFactory,
        \Magento\Eav\Model\Entity\TypeFactory $eavTypeFactory,
        \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory,
        \Magento\Eav\Model\Entity\Attribute\SetFactory $attributeSetFactory,
        \Magento\Eav\Model\Entity\Attribute\GroupFactory $attributeGroupFactory,
        \Magento\Eav\Model\AttributeManagement $attributeManagement,
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupCollectionFactory
    ) {
        $this->_categorySetupFactory = $categorySetupFactory;
        $this->_eavTypeFactory = $eavTypeFactory;
        $this->_attributeFactory = $attributeFactory;
        $this->_attributeSetFactory = $attributeSetFactory;
        $this->_attributeGroupFactory = $attributeGroupFactory;
        $this->_attributeManagement = $attributeManagement;
        $this->_eavSetupFactory = $eavSetupFactory;
        $this->_groupCollectionFactory = $groupCollectionFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        // set up

        $setup->endSetup();
    }
}
