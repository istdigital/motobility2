<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */

/**
 * Copyright © 2016 Amasty. All rights reserved.
 */

namespace Amasty\Preorder\Block\Product\View\Preorder;


class ProductGrouped extends ProductAbstract
{
    /**
     * @var array|null
     */
    private $map = null;
    /**
     * @return array
     */
    public function getGroupPreorderMap()
    {
        if ($this->map === null) {
            $this->map = [];
            foreach ($this->getAssociatedProducts() as $product) {
                /** @var \Magento\Catalog\Model\Product $product */
                if ($this->helper->getIsProductPreorder($product)) {
                    $this->map[$product->getId()] = [
                        'cartLabel' => $this->helper->getProductPreorderCartLabel($product),
                        'note'      => $this->helper->getProductPreorderNote($product),
                    ];
                }
            }
        }

        return $this->map;
    }

    /**
     * @return array
     */
    public function getAssociatedProducts()
    {
        /** @var \Magento\GroupedProduct\Model\Product\Type\Grouped $typeInstance */
        $typeInstance = $this->getProduct()->getTypeInstance();

        return $typeInstance->getAssociatedProducts($this->getProduct());
    }

    /**
     * @return string
     */
    public function getGroupedNote()
    {
        $preorderNote = '';
        if (count($this->getGroupPreorderMap())
            == count($this->getAssociatedProducts())
        ) {
            $preorderNote = $this->helper->getProductPreorderNote($this->getProduct());
        }

        return $preorderNote;
    }
}
