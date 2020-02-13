<?php

namespace TemplateMonster\ShopByBrand\Observer;

use Magento\Framework\Event\ObserverInterface;
use TemplateMonster\ShopByBrand\Api\Data\SoldBrandInterfaceFactory;
use TemplateMonster\ShopByBrand\Api\SoldBrandRepositoryInterface;

class PlaceOrderObserver implements ObserverInterface
{
    /**
     * @var \TemplateMonster\ShopByBrand\Helper\Data
     */
    protected $helper;

    /**
     * @var SoldBrandInterfaceFactory
     */
    protected $soldBrandFactory;

    /**
     * @var SoldBrandRepositoryInterface
     */
    protected $soldBrandRepository;

    /**
     * PlaceOrderObserver constructor.
     *
     * @param \TemplateMonster\ShopByBrand\Helper\Data $helper
     * @param SoldBrandInterfaceFactory                $soldBrandFactory
     * @param SoldBrandRepositoryInterface             $soldBrandRepository
     */
    public function __construct(
        \TemplateMonster\ShopByBrand\Helper\Data $helper,
        SoldBrandInterfaceFactory $soldBrandFactory,
        SoldBrandRepositoryInterface $soldBrandRepository
    ) {
        $this->helper = $helper;
        $this->soldBrandFactory = $soldBrandFactory;
        $this->soldBrandRepository = $soldBrandRepository;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $billName = $this->getBillName($order);
        $shipName = $this->getShippingName($order);
        if (!$shipName) {
            $shipName = $billName;
        }
        $storeId = $order->getStoreId();
        $orderItems = $order->getItems();

        foreach ($orderItems as $orderItem) {
            if ($orderItem->getParentItem()) {
                continue;
            }

            $product = $orderItem->getProduct();
            $brandId = $this->helper->isAssignedToBrand($product);
            if (!$brandId) {
                continue;
            }

            $originPrice = $orderItem->getRowTotalInclTax();
            $basePrice = $orderItem->getBaseRowTotalInclTax();
            $qty = $orderItem->getQtyOrdered();
            $purchasedDate = $orderItem->getCreatedAt();

            $soldBrand = $this->soldBrandFactory->create();
            $soldBrand->setName($product->getName());
            $soldBrand->setStoreId($storeId);
            $soldBrand->setBrandId($brandId);
            $soldBrand->setBillName($billName);
            $soldBrand->setShipName($shipName);
            $soldBrand->setQty($qty);
            $soldBrand->setAmount($originPrice);
            $soldBrand->setBaseAmount($basePrice);
            $soldBrand->setPurchasedDate($purchasedDate);

            try {
                $this->soldBrandRepository->save($soldBrand);
            } catch (\Exception $e) {
                continue;
            }
        }
    }

    /**
     * @param $order
     *
     * @return string
     */
    protected function getBillName($order)
    {
        $billName = '';
        $addresses = $order->getAddresses();
        foreach ($addresses as $address) {
            if ($address->getAddressType() === 'billing') {
                $billName = $address->getName();
            }
        }

        return $billName;
    }

    /**
     * @param $order
     *
     * @return string
     */
    public function getShippingName($order)
    {
        $shipName = '';
        $addresses = $order->getAddresses();
        foreach ($addresses as $address) {
            if ($address->getAddressType() === 'shipping') {
                $shipName = $address->getName();
            }
        }

        return $shipName;
    }
}
