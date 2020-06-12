<?php

namespace Zendesk\Zendesk\Model\Data;

class Order extends \Magento\Framework\Api\AbstractSimpleObject implements \Zendesk\Zendesk\Api\Data\OrderInterface
{

    /**
     * {@inheritdoc}
     */
    public function getOrderUrl()
    {
        return $this->_get(self::ORDER_URL);
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderUrl($url)
    {
        return $this->setData(self::ORDER_URL, $url);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return $this->_get(self::ORDER);
    }

    /**
     * {@inheritdoc}
     */
    public function setOrder(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        return $this->setData(self::ORDER, $order);
    }

    /**
     * {@inheritDoc}
     */
    public function getCustomerBackendUrl()
    {
        return $this->_get(self::CUSTOMER_BACKEND_URL);
    }

    /**
     * {@inheritDoc}
     */
    public function setCustomerBackendUrl($url)
    {
        return $this->setData(self::CUSTOMER_BACKEND_URL, $url);
    }
}
