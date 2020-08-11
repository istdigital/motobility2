<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */


namespace Amasty\Preorder\Block\Adminhtml\Product\Edit\Action\Attribute\Tab\Inventory;

class PreOrderJs extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_Preorder::product_inventory_js.phtml';

    /**
     * @return int
     */
    public function getPreorderId()
    {
        return \Amasty\Preorder\Helper\Data::BACKORDERS_PREORDER_OPTION;
    }
}
