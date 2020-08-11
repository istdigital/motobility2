<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */


namespace Amasty\Preorder\Observer;

use Magento\Framework\Event\Observer;

class OrderPlaceAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Amasty\Preorder\Helper\Data
     */
    private $dataHelper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        \Amasty\Preorder\Helper\Data $helper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->dataHelper = $helper;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if (!$this->dataHelper->preordersEnabled()) {
            return;
        }

        $order = $observer->getEvent()->getOrder();
        try {
            $this->dataHelper->checkNewOrder($order);
        } catch (\Exception $ex) {
            $this->logger->critical($ex);
        }
    }
}
