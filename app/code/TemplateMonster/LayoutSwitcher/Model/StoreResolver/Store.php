<?php

namespace TemplateMonster\LayoutSwitcher\Model\StoreResolver;

use TemplateMonster\LayoutSwitcher\Helper\Data as LayoutSwitcherHelper;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\StoreResolver\Store as BaseStore;

/**
 * Class Store
 *
 * @package TemplateMonster\LayoutSwitcher\Model\StoreResolver
 */
class Store extends BaseStore
{
    /**
     * @var LayoutSwitcherHelper
     */
    protected $_helper;

    /**
     * @var WebsiteRepositoryInterface
     */
    protected $_websiteRepository;

    /**
     * Store constructor.
     *
     * @param LayoutSwitcherHelper       $helper
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param StoreRepositoryInterface   $storeRepository
     */
    public function __construct(
        LayoutSwitcherHelper $helper,
        WebsiteRepositoryInterface $websiteRepository,
        StoreRepositoryInterface $storeRepository
    ) {
        $this->_helper = $helper;
        $this->_websiteRepository = $websiteRepository;
        parent::__construct($storeRepository);
    }

    /**
     * @inheritdoc
     */
    public function getDefaultStoreId($scopeCode)
    {
        if ($scopeCode = $this->_helper->getDefaultHomepage()) {
            try {
                return parent::getDefaultStoreId($scopeCode);
            }
            catch (\Exception $e) {
                //TODO: log error message
            }
        }

        /** @var \Magento\Store\Model\Website $website */
        $website = $this->_websiteRepository->getDefault();

        return $website->getDefaultStore()->getId();
    }
}