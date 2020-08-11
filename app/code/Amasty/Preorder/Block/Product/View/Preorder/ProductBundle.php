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

class ProductBundle extends ProductAbstract
{

    protected $_bundleOptionsData;
    protected $_bundleSelectionsData;

    protected $_isAllProductsPreorder;

    /**
     * @return array
     */
    public function getBundleSelectionsData()
    {
        if (is_null($this->_bundleSelectionsData)) {
            $this->prepareBundleData();
        }
        return $this->_bundleSelectionsData;
    }

    /**
     * @return array
     */
    public function getBundleOptionsData()
    {
        if (is_null($this->_bundleOptionsData)) {
            $this->prepareBundleData();
        }
        return $this->_bundleOptionsData;
    }

    /**
     * @return bool
     */
    public function getIsAllProductsPreorder()
    {
        if(is_null($this->_isAllProductsPreorder)) {
            $this->prepareBundleData();
        }

        return $this->_isAllProductsPreorder;
    }

    protected function prepareBundleData()
    {
        $this->_bundleSelectionsData = [];
        $this->_bundleOptionsData = [];

        /** @var \Magento\Bundle\Model\Product\Type $typeInstance */
        $typeInstance = $this->getProduct()->getTypeInstance();

        $optionIds = $typeInstance->getOptionsIds($this->getProduct());
        $options = $typeInstance->getOptions($this->getProduct());
        foreach($options as $option) {
            /** @var $option \Magento\Bundle\Model\Option */
            $this->_bundleOptionsData[$option->getId()] = array(
                'isSingle' => null,
                'isMultiSelection' => (bool) $option->isMultiSelection(),
                'isRequired' => (bool) $option->getRequired(),
                'selectionCount' => 0, // for a while
                'isPreorder' => null,
                'message' => null,
                'selectionId' => 0,
            );
        }

        $selections = $typeInstance->getSelectionsCollection($optionIds, $this->getProduct());
        $productIds = [];
        foreach ($selections as $selection) {
            $productIds[] = $selection->getProductId();
        }

        $products = $this->getProduct()->getCollection()->addFieldToFilter('entity_id', $productIds);
        $this->_isAllProductsPreorder = true;
        foreach($selections as $selection) {
            /** @var \Magento\Catalog\Model\Product $product */
            /** @var \Magento\Bundle\Model\Selection $selection */
            $product = $products->getItemById($selection->getProductId());
            if ($product === null) {
                continue;
            }

            $isPreorder = $this->helper->getIsProductPreorder($product);
            if(!$isPreorder) {
                $this->_isAllProductsPreorder = false;
            }
            
            $note = $this->helper->getProductPreorderNote($product);
            $cartLabel = $this->helper->getProductPreorderCartLabel($product);

            $this->_bundleSelectionsData[$selection->getSelectionId()] = array(
                'isPreorder' => $isPreorder,
                'note' => $note,
                'cartLabel' => $cartLabel,
                'optionId' => $selection->getOptionId(),
            );

            // Update option record
            $optionRecord = &$this->_bundleOptionsData[$selection->getOptionId()];
            $optionRecord['selectionCount']++;
            $optionRecord['isSingle'] = $optionRecord['selectionCount'] == 1;

            if ($optionRecord['isSingle']) {
                $optionRecord['isPreorder'] = $isPreorder;
                $optionRecord['message'] = $note;
                $optionRecord['selectionId'] = $selection->getSelectionId();
            } else {
                // Have to analyze selections on frontend in order to find out
                $optionRecord['isPreorder'] = null;
                $optionRecord['message'] = null;
            }
        }
    }

    /**
     * @return array
     */
    public function getMap()
    {
        $selectionsPreorderMap = [];
        /** @var \Magento\Bundle\Model\Product\Type $typeInstance */
        $typeInstance = $this->getProduct()->getTypeInstance();
        $optionIds = $typeInstance->getOptionsIds($this->getProduct());
        $selections = $typeInstance->getSelectionsCollection($optionIds, $this->getProduct());
        $productIds = [];
        foreach ($selections as $selection) {
            $productIds[] = $selection->getProductId();
        }
        $products = $this->getProduct()->getCollection()->addFieldToFilter('entity_id', $productIds);
        foreach($selections as $selection) {
            /** @var \Magento\Catalog\Model\Product $product */
            /** @var \Magento\Bundle\Model\Selection $selection */
            $product = $products->getItemById($selection->getProductId());
            if ($product === null) {
                continue;
            }

            $isPreorder = $this->helper->getIsProductPreorder($product);
            if(!$isPreorder) {
                continue;
            }
            $selectionsPreorderMap[$selection->getOptionId().'-'.$selection->getSelectionId()] = [
                'note' => $this->helper->getProductPreorderNote($product),
            ];
        }
        return $selectionsPreorderMap;
    }

    public function canShowBlock()
    {
        $result = $this->getProduct()->isSalable() && parent::canShowBlock();

        return $result;
    }
}
