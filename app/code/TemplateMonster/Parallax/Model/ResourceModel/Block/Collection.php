<?php

namespace TemplateMonster\Parallax\Model\ResourceModel\Block;

use TemplateMonster\Parallax\Model\ResourceModel\AbstractCollection;

/**
 * Parallax Block Resource Collection.
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'block_id';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('TemplateMonster\Parallax\Model\Block', 'TemplateMonster\Parallax\Model\ResourceModel\Block');
    }

    /**
     * Add filter by store.
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool                                 $withAdmin
     *
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
        }

        return $this;
    }

    /**
     * Perform operations after collection load.
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $this->performAfterLoad('parallax_block_store', 'block_id');

        return parent::_afterLoad();
    }

    /**
     * Perform operations before rendering filters.
     */
    protected function _renderFiltersBefore()
    {
        $this->joinStoreRelationTable('parallax_block_store', 'block_id');
    }
}
