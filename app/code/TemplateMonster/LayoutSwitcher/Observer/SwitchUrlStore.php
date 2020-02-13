<?php

namespace TemplateMonster\LayoutSwitcher\Observer;

use TemplateMonster\LayoutSwitcher\Helper\Data as LayoutSwitcherHelper;
use Magento\Store\Model\StoreIsInactiveException;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class SwitchUrlStore
 *
 * @package TemplateMonster\LayoutSwitcher\Observer
 */
class SwitchUrlStore implements ObserverInterface
{
    /**
     * @var StoreCookieManagerInterface
     */
    protected $_storeCookieManager;

    /**
     * @var StoreRepositoryInterface
     */
    protected $_storeRepository;

    /**
     * @var LayoutSwitcherHelper
     */
    protected $_helper;

    /**
     * SwitchUrlStore constructor.
     *
     * @param StoreCookieManagerInterface $storeCookieManager
     * @param StoreRepositoryInterface    $storeRepository
     * @param LayoutSwitcherHelper        $helper
     */
    public function __construct(
        StoreCookieManagerInterface $storeCookieManager,
        StoreRepositoryInterface $storeRepository,
        LayoutSwitcherHelper $helper
    )
    {
        $this->_storeCookieManager = $storeCookieManager;
        $this->_storeRepository = $storeRepository;
        $this->_helper = $helper;
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        if ($this->_helper->isEnabled()) {
            /** @var \Magento\Framework\App\RequestInterface $request */
            $request = $observer->getData('request');

            if ($storeCode = $request->getParam(StoreResolverInterface::PARAM_NAME)) {
                $store = $this->_getRequestedStoreByCode($storeCode);
                $this->_storeCookieManager->setStoreCookie($store);
            }
        }
    }

    /**
     * Get requested store by code.
     *
     * @param $storeCode
     *
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws NoSuchEntityException
     */
    protected function _getRequestedStoreByCode($storeCode)
    {
        try {
            $store = $this->_storeRepository->getActiveStoreByCode($storeCode);
        } catch (StoreIsInactiveException $e) {
            throw new NoSuchEntityException(__('Requested store is inactive.'));
        }

        return $store;
    }
}