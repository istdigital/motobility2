<?php

namespace Cryozonic\StripePayments\Model\ResourceModel\StripeCustomer;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Cryozonic\StripePayments\Model\StripeCustomer', 'Cryozonic\StripePayments\Model\ResourceModel\StripeCustomer');
    }
}