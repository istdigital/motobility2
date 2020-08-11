<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */


namespace Amasty\Preorder\Block\Product\View\Preorder;

class ProductConfigurable extends ProductAbstract
{
    /**
     * @return array
     */
    public function getConfigurableAttributes()
    {
        $attributes = [];
        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $typeInstance */
        $typeInstance      = $this->getProduct()->getTypeInstance();
        $allowedAttributes = $typeInstance->getConfigurableAttributes($this->getProduct());
        foreach ($allowedAttributes as $attribute) {
            $attributes[$attribute->getProductAttribute()->getId()] = 0;
        }

        return $attributes;
    }

    /**
     * @return array
     */
    public function getProductPreorderMap()
    {
        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $typeInstance */
        $typeInstance       = $this->getProduct()->getTypeInstance();
        $elementaryProducts = $typeInstance->getUsedProducts($this->getProduct());
        $allowedAttributes  = $typeInstance->getConfigurableAttributes($this->getProduct());

        $map = [];
        $elementaryProducts[] = $this->getProduct();
        foreach ($elementaryProducts as $product) {
            /** @var \Magento\Catalog\Model\Product $product */
            if ($this->helper->getIsProductPreorder($product)) {
                $stockItem = $this->stockRegistry->getStockItem($product->getId());
                if (!$stockItem->getIsInStock()) {
                    continue;
                }

                $map[$product->getId()] = [
                    'cartLabel'  => $this->helper->getProductPreorderCartLabel($product),
                    'note'       => $this->helper->getProductPreorderNote($product),
                    'attributes' => []
                ];

                foreach ($allowedAttributes as $attribute) {
                    $productAttribute   = $attribute->getProductAttribute();
                    $productAttributeId = $productAttribute->getId();
                    $attributeValue     = $product->getData($productAttribute->getAttributeCode());

                    $map[$product->getId()]['attributes'][$productAttributeId] = $attributeValue;
                }
            }
        }

        return $map;
    }

    /**
     * Is All children of Configurable are in Preorder status
     *
     * @return bool
     */
    public function getIsAllProductsPreorder()
    {
        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $typeInstance */
        $typeInstance          = $this->getProduct()->getTypeInstance();
        $elementaryProducts    = $typeInstance->getUsedProducts($this->getProduct());
        $isAllProductsPreorder = true;
        foreach ($elementaryProducts as $product) {
            if (!$this->helper->getIsProductPreorder($product)) {
                $isAllProductsPreorder = false;
                break;
            }
        }

        return $isAllProductsPreorder;
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        return $this->getProduct()->getId();
    }

    /**
     * @return string
     */
    public function getProductSku()
    {
        return $this->getProduct()->getSku();
    }

    /**
     * @return int
     */
    public function getEntityId()
    {
        return $this->getProduct()->getEntityId();
    }

    /**
     * @return string
     */
    public function getJsonProductPreorderMap()
    {
        return $this->jsonEncoder->encode($this->getProductPreorderMap());
    }

    /**
     * @return string
     */
    public function getJsonCurrentAttributeArray()
    {
        return $this->jsonEncoder->encode(([$this->getEntityId() => $this->getConfigurableAttributes()]));
    }

    /**
     * @return string
     */
    public function getJsonCurrentAttribute()
    {
        return $this->jsonEncoder->encode($this->getConfigurableAttributes());
    }
}
