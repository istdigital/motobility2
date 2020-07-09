<?php

namespace StripeIntegration\Payments\Model\ResourceModel\Webhook;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';

    protected function _construct()
    {
        $this->_init('StripeIntegration\Payments\Model\Subscription', 'StripeIntegration\Payments\Model\ResourceModel\Subscription');
    }
}
