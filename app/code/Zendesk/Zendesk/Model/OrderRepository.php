<?php

namespace Zendesk\Zendesk\Model;

class OrderRepository implements \Zendesk\Zendesk\Api\OrderRepositoryInterface
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    /**
     * @var \Zendesk\Zendesk\Api\Data\OrderInterfaceFactory
     */
    protected $zendeskOrderFactory;
    /**
     * @var \Zendesk\Zendesk\Helper\Data
     */
    protected $helper;
    /**
     * @var \Magento\Framework\Api\SortOrderBuilder
     */
    protected $sortOrderBuilder;
    /**
     * @var \Zendesk\Zendesk\Helper\Config
     */
    protected $configHelper;

    /**
     * OrderRepository constructor.
     * @param \Zendesk\Zendesk\Api\Data\OrderInterfaceFactory $zendeskOrderFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder
     * @param \Zendesk\Zendesk\Helper\Data $helper
     * @param \Zendesk\Zendesk\Helper\Config $configHelper
     */
    public function __construct(
        \Zendesk\Zendesk\Api\Data\OrderInterfaceFactory $zendeskOrderFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder,
        \Zendesk\Zendesk\Helper\Data $helper,
        \Zendesk\Zendesk\Helper\Config $configHelper
    ) {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->zendeskOrderFactory = $zendeskOrderFactory;
        $this->helper = $helper;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->configHelper = $configHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrders($emailAddress, $brandId, $orderCount)
    {
        $this->searchCriteriaBuilder->addFilter(
            \Magento\Sales\Api\Data\OrderInterface::CUSTOMER_EMAIL,
            $emailAddress
        );

        $storeIds = $this->configHelper->getBrandStores($brandId);
        if (!empty($storeIds)) {
            $this->searchCriteriaBuilder->addFilter(
                \Magento\Sales\Api\Data\OrderInterface::STORE_ID,
                $storeIds,
                'in'
            );
        }

        $this->sortOrderBuilder->setField(\Magento\Sales\Api\Data\OrderInterface::CREATED_AT);
        $this->sortOrderBuilder->setDescendingDirection();

        $this->searchCriteriaBuilder->addSortOrder($this->sortOrderBuilder->create());

        $this->searchCriteriaBuilder->setPageSize($orderCount);

        $orders = $this->orderRepository->getList($this->searchCriteriaBuilder->create())->getItems();

        /** @var \Zendesk\Zendesk\Api\Data\OrderInterface[] $zendeskOrders */
        $zendeskOrders = [];

        foreach ($orders as $order) {
            /** @var \Zendesk\Zendesk\Api\Data\OrderInterface $zendeskOrder */
            $zendeskOrder = $this->zendeskOrderFactory->create();

            $zendeskOrder->setOrderUrl($this->helper->getOrderDeepLinkUrl($order->getEntityId()));

            if (!empty($order->getCustomerId())) {
                $zendeskOrder->setCustomerBackendUrl($this->helper->getCustomerDeepLinkUrl($order->getCustomerId()));
            }

            $zendeskOrder->setOrder($order);

            $zendeskOrders[] = $zendeskOrder;
        }

        return $zendeskOrders;
    }
}
