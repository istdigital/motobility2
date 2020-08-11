<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */


namespace Amasty\Preorder\Plugin\ProductList;

class Related extends \Amasty\Preorder\Plugin\AbstractProductList
{
    /**
     * @param \Magento\Catalog\Block\Product\ProductList\Related $subject
     * @param string $resultHtml
     *
     * @return string
     */
    public function afterToHtml($subject, $resultHtml)
    {
        $this->setProductCollection($subject->getItems());

        return parent::afterToHtml($subject, $resultHtml);
    }
}
