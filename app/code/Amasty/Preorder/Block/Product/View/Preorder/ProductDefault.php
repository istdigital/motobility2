<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */


namespace Amasty\Preorder\Block\Product\View\Preorder;

class ProductDefault extends ProductAbstract
{
    /**
     * @return bool
     */
    public function canShowBlock()
    {
        return parent::canShowBlock() && $this->helper->getIsProductPreorder($this->getProduct());
    }
}
