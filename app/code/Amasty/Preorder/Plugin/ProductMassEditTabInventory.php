<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */


namespace Amasty\Preorder\Plugin;

use Amasty\Preorder\Block\Adminhtml\Product\Edit\Action\Attribute\Tab\Inventory\PreOrder;
use Amasty\Preorder\Block\Adminhtml\Product\Edit\Action\Attribute\Tab\Inventory\PreOrderJs;

/**
 * Plugin for class \Magento\Catalog\Block\Adminhtml\Product\Edit\Action\Attribute\Tab\Inventory
 */
class ProductMassEditTabInventory
{
    /**
     * @var \Magento\Eav\Model\Entity\AttributeFactory
     */
    private $attributeFactory;

    public function __construct(\Magento\Eav\Model\Entity\AttributeFactory $attributeFactory)
    {
        $this->attributeFactory = $attributeFactory;
    }

    /**
     * Add Pre-Order text attributes to Inventory tab
     *
     * @param \Magento\Catalog\Block\Adminhtml\Product\Edit\Action\Attribute\Tab\Inventory $subject
     * @param string $html
     *
     * @return string
     */
    public function afterToHtml(
        \Magento\Catalog\Block\Adminhtml\Product\Edit\Action\Attribute\Tab\Inventory $subject,
        $html
    ) {
        $preOrderHtml = $subject->getLayout()
            ->createBlock(PreOrder::class)
            ->toHtml();

        $preorderJsHtml = $subject->getLayout()
            ->createBlock(PreOrderJs::class)
            ->toHtml();

        $html .= $preOrderHtml . PHP_EOL . $preorderJsHtml;

        return $html;
    }
}
