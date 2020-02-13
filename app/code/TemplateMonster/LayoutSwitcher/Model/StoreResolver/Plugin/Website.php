<?php

namespace TemplateMonster\LayoutSwitcher\Model\StoreResolver\Plugin;

use Magento\Framework\App\RequestInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\StoreResolver\Website as BaseWebsite;

/**
 * Website store resolver plugin.
 *
 * @package TemplateMonster\LayoutSwitcher\Model\StoreResolver\Plugin
 */
class Website
{
    /**
     * Livedemo init param.
     */
    const PARAM_MODE = 'LIVEDEMO_MODE';

    /**
     * @var StoreRepositoryInterface
     */
    protected $_storeRepository;

    /**
     * Livedemo flag.
     *
     * @var bool
     */
    protected $_livedemoMode;

    /**
     * Website constructor.
     *
     * @param StoreRepositoryInterface $storeRepository
     * @param RequestInterface         $request
     * @param bool                     $livedemoMode
     */
    public function __construct(
        StoreRepositoryInterface $storeRepository,
        RequestInterface $request,
        $livedemoMode = false
    )
    {
        $this->_storeRepository = $storeRepository;
        $this->_livedemoMode = (bool) $livedemoMode;
    }

    /**
     * @param BaseWebsite $subject
     * @param callable    $proceed
     * @param string      $scopeCode
     *
     * @return array
     */
    public function aroundGetAllowedStoreIds(BaseWebsite $subject, callable $proceed, $scopeCode)
    {
        if ($this->_livedemoMode) {
            return $this->_getAllowedStoreIds();
        }

        return $proceed($scopeCode);
    }

    /**
     * Overridden method implementation to make work multiple websites per 1 host.
     *
     * @return array
     */
    protected function _getAllowedStoreIds()
    {
        $stores = [];
        foreach ($this->_storeRepository->getList() as $store) {
            $stores[] = $store->getId();
        }

        return $stores;
    }
}