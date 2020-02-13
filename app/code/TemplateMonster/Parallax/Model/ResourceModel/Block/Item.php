<?php

namespace TemplateMonster\Parallax\Model\ResourceModel\Block;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Block item entity model.
 *
 * @package TemplateMonster\Parallax\Model\ResourceModel\Block
 */
class Item extends AbstractDb
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('parallax_block_item', 'item_id');
    }
}
