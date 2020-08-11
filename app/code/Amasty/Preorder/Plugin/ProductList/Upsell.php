<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */


namespace Amasty\Preorder\Plugin\ProductList;

class Upsell extends \Amasty\Preorder\Plugin\AbstractProductList
{
    /**
     * @param \Magento\Catalog\Block\Product\ProductList\Upsell $subject
     * @param string $resultHtml
     *
     * @return string
     */
    public function afterToHtml($subject, $resultHtml)
    {
        $this->setProductCollection($subject->getItemCollection());

        return parent::afterToHtml($subject, $resultHtml);
    }
}
