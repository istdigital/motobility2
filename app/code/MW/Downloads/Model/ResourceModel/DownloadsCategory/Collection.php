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

namespace MW\Downloads\Model\ResourceModel\DownloadsCategory;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('MW\Downloads\Model\DownloadsCategory', 'MW\Downloads\Model\ResourceModel\DownloadsCategory');
    }

    /**
     * @inheritdoc
     */
    protected function _initSelect()
    {       
        $where = ''; 
        if(isset($_GET['query']) && !empty($_GET['query'])){
            $where = " WHERE `title` LIKE '%{$_GET['query']}%' OR `tags` LIKE '%{$_GET['query']}%' ";
        }

        $this->getSelect()->from(['main_table' => $this->getMainTable()])
        ->joinLeft(
            ['download_count' => new \Zend_Db_Expr("(SELECT category_id,COUNT(*) as total FROM mw_downloads $where  GROUP BY category_id)")],
            'main_table.category_id = download_count.category_id',
            ['download_count.total']
        );

        return $this;
    }

    public function getBySortOrder()
    {
        $this->getSelect()->reset('order');
        return $this->setOrder('sort_order', 'asc');
    }
}