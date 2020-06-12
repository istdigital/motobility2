<?php

namespace Zendesk\Zendesk\Api\Data;

interface OrderInterface
{
    const ORDER_URL = 'order_url';
    const ORDER = 'order';
    const CUSTOMER_BACKEND_URL = 'customer_backend_url';

    /**
     * Get order backend details URL
     *
     * @return string
     */
    public function getOrderUrl();

    /**
     * Set order backend details URL
     *
     * @param string $url
     * @return $this
     */
    public function setOrderUrl($url);

    /**
     * Get customer backend URL
     *
     * @return string
     */
    public function getCustomerBackendUrl();

    /**
     * Set customer backend URL
     *
     * @param string $url
     * @return $this
     */
    public function setCustomerBackendUrl($url);

    /**
     * Get order instance
     *
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrder();

    /**
     * Set order instance
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return $this
     */
    public function setOrder(\Magento\Sales\Api\Data\OrderInterface $order);
}
