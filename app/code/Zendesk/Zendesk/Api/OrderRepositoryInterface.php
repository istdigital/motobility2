<?php

namespace Zendesk\Zendesk\Api;

interface OrderRepositoryInterface
{
    /**
     * Get customers orders for given brand's stores,
     * ordered by creation date descending.
     *
     * @api
     * @param string $emailAddress
     * @param int $brandId
     * @param int $orderCount
     * @return \Zendesk\Zendesk\Api\Data\OrderInterface[]
     */
    public function getOrders($emailAddress, $brandId, $orderCount);
}
