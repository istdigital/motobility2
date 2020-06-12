<?php

namespace Zendesk\Zendesk\Api\Data;

interface CustomerInterface
{
    const CUSTOMER_URL = 'customer_url';
    const CUSTOMER = 'customer';

    /**
     * Get customer backend details URL
     *
     * @return string
     */
    public function getCustomerUrl();

    /**
     * Set customer backend details URL
     *
     * @param string $url
     * @return $this
     */
    public function setCustomerUrl($url);

    /**
     * Get customer instance
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getCustomer();

    /**
     * Set customer instance
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return $this
     */
    public function setCustomer(\Magento\Customer\Api\Data\CustomerInterface $customer);
}
