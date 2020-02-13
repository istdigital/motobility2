<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Zemez\StockAlert\Model\ResourceModel\Subscriber;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * CMS Block Collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'stock_alert_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'alert_collection';


    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Zemez\StockAlert\Model\Subscriber::class, \Zemez\StockAlert\Model\ResourceModel\Subscriber::class);
    }

}
