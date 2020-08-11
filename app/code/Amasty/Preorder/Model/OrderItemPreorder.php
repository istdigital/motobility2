<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */

/**
 * Copyright © 2016 Amasty. All rights reserved.
 */

namespace Amasty\Preorder\Model;


class OrderItemPreorder extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Amasty\Preorder\Model\ResourceModel\OrderItemPreorder');
    }

    public function loadByItemId($itemId)
    {
        $this->getResource()->load($this, $itemId, 'order_item_id');
    }
}
