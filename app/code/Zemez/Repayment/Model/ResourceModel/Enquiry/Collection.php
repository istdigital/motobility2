<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Zemez\Repayment\Model\ResourceModel\Enquiry;

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
    protected $_eventPrefix = 'repayment_enquiries';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'repayment_enquiries';


    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Zemez\Repayment\Model\Enquiry::class, \Zemez\Repayment\Model\ResourceModel\Enquiry::class);
    }

    public function getSelect()
    {
        return parent::getSelect()->order('date DESC');
    }

}
