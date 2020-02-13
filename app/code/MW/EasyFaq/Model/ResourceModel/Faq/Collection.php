<?php

namespace MW\EasyFaq\Model\ResourceModel\Faq;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('MW\EasyFaq\Model\Faq', 'MW\EasyFaq\Model\ResourceModel\Faq');
    }

    /**
     * @inheritdoc
     */
    protected function _initSelect()
    {
        $this->getSelect()->from(['main_table' => $this->getMainTable()])
        ->joinLeft(
            ['category' => new \Zend_Db_Expr("(SELECT `category_id` as `cat_id`, `name` FROM `mw_faq_category`)")],
            'main_table.category_id = category.cat_id',
            ['category.name as category_name']
        );

        return $this;
    }

    public function getBySortOrder()
    {
        $this->getSelect()->reset('order');
        return $this->setOrder('sort_order', 'asc');
    }
}