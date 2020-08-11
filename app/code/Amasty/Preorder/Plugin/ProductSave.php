<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */


namespace Amasty\Preorder\Plugin;

class ProductSave
{
    /**
     * @var \Amasty\Preorder\Model\ResourceModel\Product\Attribute\UpdateHandler
     */
    private $updateHandler;

    public function __construct(
        \Amasty\Preorder\Model\ResourceModel\Product\Attribute\UpdateHandler $updateHandler
    ) {
        $this->updateHandler = $updateHandler;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product $subject
     * @param \Closure $closure
     * @param \Magento\Catalog\Model\Product  $product
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product
     */
    public function aroundSave(\Magento\Catalog\Model\ResourceModel\Product $subject, \Closure $closure, $product)
    {
        $note = $product->getData('amasty_preorder_note');
        $origNote = $product->getOrigData('amasty_preorder_note');
        $label = $product->getData('amasty_preorder_cart_label');
        $origLabel = $product->getOrigData('amasty_preorder_cart_label');
        $result = $closure($product);

        if ($product->getData('amasty_preorder_note') != $note) {
            $origNote = $product->getData('amasty_preorder_note');
        }
        if ($product->getData('amasty_preorder_cart_label') != $label) {
            $origLabel = $product->getData('amasty_preorder_cart_label');
        }

        if ($note != $origNote || $label != $origLabel) {
            $product->setData('amasty_preorder_note', $note);
            $product->setData('amasty_preorder_cart_label', $label);
            $this->updateHandler->execute(\Magento\Catalog\Api\Data\ProductInterface::class, $product->getData());
        }

        return $result;
    }
}
