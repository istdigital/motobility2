<?php

namespace Zendesk\Zendesk\Model\Data;

use Magento\Framework\Api\AbstractSimpleObject;

class Customer extends AbstractSimpleObject implements \Zendesk\Zendesk\Api\Data\CustomerInterface
{

    /**
     * {@inheritdoc}
     */
    public function getCustomerUrl()
    {
        return $this->_get(self::CUSTOMER_URL);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerUrl($url)
    {
        return $this->setData(self::CUSTOMER_URL, $url);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomer()
    {
        return $this->_get(self::CUSTOMER);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomer(\Magento\Customer\Api\Data\CustomerInterface $order)
    {
        return $this->setData(self::CUSTOMER, $order);
    }
}
