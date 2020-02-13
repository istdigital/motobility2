<?php

namespace Manadev\Core\Traits;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ObjectManager;
use Manadev\Core\Features;
use Manadev\Core\Helpers\DbHelper;
use Manadev\LayeredNavigation\Helper;
use Manadev\ProductCollection\FacetSorter;
use Manadev\ProductCollection\Resources\HelperResource;

/**
 * @property ObjectManagerInterface $objectManager
 */
trait FrontendDependencies
{
    protected function getObjectManager() {
        return ObjectManager::getInstance();
    }

    /**
     * @return StoreManagerInterface
     */
    protected function getStoreManager() {
        return $this->getObjectManager()->get(StoreManagerInterface::class);
    }

    protected function getWebsite() {
        return $this->getStoreManager()->getWebsite();
    }

    protected function getWebsiteId() {
        return $this->getWebsite()->getId();
    }

    /**
     * @return CustomerSession
     */
    protected function getCustomerSession() {
        return $this->getObjectManager()->get(CustomerSession::class);
    }

    protected function getCustomerGroupId() {
        return $this->getCustomerSession()->getCustomerGroupId();
    }

    /**
     * @return Store|StoreInterface
     */
    protected function getStore() {
        return $this->getStoreManager()->getStore();
    }

    protected function getStoreId() {
        return $this->getStore()->getId();
    }

    protected function isFeatureEnabled($featureName) {
        return $this->getObjectManager()->get(Features::class)->isEnabled($featureName);
    }

    /**
     * @return DbHelper
     */
    protected function getDbHelper() {
        return $this->getObjectManager()->get(DbHelper::class);
    }

    /**
     * @return HelperResource
     */
    protected function getLayeredHelperResource() {
        return $this->getObjectManager()->get(HelperResource::class);
    }

    /**
     * @return Helper
     */
    protected function getLayeredHelper() {
        return $this->getObjectManager()->get(Helper::class);
    }

    /**
     * @return \Manadev\ProductCollection\Configuration
     */
    protected function getProductCollectionConfiguration() {
        return $this->getObjectManager()->get(\Manadev\ProductCollection\Configuration::class);
    }

    /**
     * @return FacetSorter
     */
    protected function getLayeredSorter() {
        return $this->getObjectManager()->get(FacetSorter::class);
    }

    /**
     * @return \Magento\Framework\Search\Request\Config
     */
    protected function getSearchRequestConfig() {
        return $this->getObjectManager()->get(\Magento\Framework\Search\Request\Config::class);
    }

    /**
     * @return \Magento\Elasticsearch\Model\Adapter\FieldMapperInterface
     */
    protected function getElasticFieldMapper() {
        return $this->getObjectManager()->get(\Magento\Elasticsearch\Model\Adapter\FieldMapperInterface::class);
    }
}