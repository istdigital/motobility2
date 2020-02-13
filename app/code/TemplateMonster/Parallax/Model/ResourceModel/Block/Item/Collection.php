<?php

namespace TemplateMonster\Parallax\Model\ResourceModel\Block\Item;

use TemplateMonster\Parallax\Api\Data\BlockInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Block item collection.
 *
 * @package TemplateMonster\Parallax\Model\ResourceModel\Block\Item
 */
class Collection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('TemplateMonster\Parallax\Model\Block\Item', 'TemplateMonster\Parallax\Model\ResourceModel\Block\Item');
    }

    /**
     * Add block filter.
     *
     * @param BlockInterface $block
     *
     * @return $this
     */
    public function addBlockFilter(BlockInterface $block)
    {
        return $this->addFieldToFilter('block_id', $block->getId());
    }

    /**
     * Add enabled filter.
     *
     * @return $this
     */
    public function addEnabledFilter()
    {
        return $this->addFieldToFilter('status', BlockInterface::STATUS_ENABLED);
    }
}
