<?php
/**
 * Mage-World
 *
 *  @category    Mage-World
 *  @package     MW
 *  @author      Mage-world Developer
 *
 *  @copyright   Copyright (c) 2018 Mage-World (https://www.mage-world.com/)
 */

namespace MW\EasyFaq\Model\ResourceModel\FaqCategory;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('MW\EasyFaq\Model\FaqCategory', 'MW\EasyFaq\Model\ResourceModel\FaqCategory');
    }

    /**
     * @inheritdoc
     */
    protected function _initSelect()
    {       
        $where = ''; 
        if(isset($_GET['query']) && !empty($_GET['query'])){
            $where = " WHERE `question` LIKE '%{$_GET['query']}%' OR `answer` LIKE '%{$_GET['query']}%' ";
        }

        $this->getSelect()->from(['main_table' => $this->getMainTable()])
        ->joinLeft(
            ['faq_count' => new \Zend_Db_Expr("(SELECT category_id,COUNT(*) as total FROM mw_faq $where  GROUP BY category_id)")],
            'main_table.category_id = faq_count.category_id',
            ['faq_count.total']
        );

        return $this;
    }

    public function getBySortOrder()
    {
        $this->getSelect()->reset('order');
        return $this->setOrder('sort_order', 'asc');
    }
}