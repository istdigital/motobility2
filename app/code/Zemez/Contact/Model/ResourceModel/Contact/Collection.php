<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Zemez\Contact\Model\ResourceModel\Contact;

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
    protected $_eventPrefix = 'contact_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'contact_collection';


    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Zemez\Contact\Model\Contact::class, \Zemez\Contact\Model\ResourceModel\Contact::class);
    }

    public function getSelect()
    {
        return parent::getSelect()->order('date DESC');
    }

}
