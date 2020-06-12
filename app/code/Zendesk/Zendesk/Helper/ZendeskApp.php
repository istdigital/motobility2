<?php

namespace Zendesk\Zendesk\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;

class ZendeskApp extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Api
     */
    protected $apiHelper;
    /**
     * @var Config
     */
    protected $configHelper;
    /**
     * @var Integration
     */
    protected $integrationHelper;
    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    protected $storeRepository;
    /**
     * @var \Magento\Store\Api\WebsiteRepositoryInterface
     */
    protected $websiteRepository;
    /**
     * @var Data
     */
    protected $helper;
    /**
     * @var \Magento\Store\Api\GroupRepositoryInterface
     */
    protected $storeGroupRepository;

    /**
     * @var \stdClass|null
     */
    protected $zendeskAppInstance = null;

    /**
     * ZendeskApp constructor.
     * @param Context $context
     * @param Api $apiHelper
     * @param Config $configHelper
     * @param Integration $integrationHelper
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     * @param \Magento\Store\Api\GroupRepositoryInterface $storeGroupRepository
     * @param \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        // End parent parameters
        \Zendesk\Zendesk\Helper\Api $apiHelper,
        \Zendesk\Zendesk\Helper\Config $configHelper,
        \Zendesk\Zendesk\Helper\Integration $integrationHelper,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        \Magento\Store\Api\GroupRepositoryInterface $storeGroupRepository,
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository,
        \Zendesk\Zendesk\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->apiHelper = $apiHelper;
        $this->configHelper = $configHelper;
        $this->integrationHelper = $integrationHelper;
        $this->storeRepository = $storeRepository;
        $this->websiteRepository = $websiteRepository;
        $this->helper = $helper;
        $this->storeGroupRepository = $storeGroupRepository;
    }

    /**
     * Get Zendesk app instance or `null` if not installed.
     *
     * @param string $scopeType
     * @param null $scopeCode
     * @return \stdClass|null
     * @throws \Zendesk\API\Exceptions\AuthException
     */
    protected function getZendeskAppInstance($scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        if ($this->zendeskAppInstance === null) {
            $appId = $this->configHelper->getZendeskAppId();

            $api = $this->apiHelper->getZendeskApiInstance($scopeType, $scopeCode);

            $installedApps = $api->apps()->getInstalledApps();

            foreach ($installedApps->installations as $installedApp) {
                if ($installedApp->app_id == $appId) {
                    $this->zendeskAppInstance = $installedApp;
                    break;
                }
            }
        }

        return $this->zendeskAppInstance;
    }

    /**
     * Get if Zendesk App is currently installed in Zendesk support.
     *
     * @param string $scopeType
     * @param null $scopeCode
     * @return bool
     */
    public function isZendeskAppInstalled(
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        try {
            return $this->getZendeskAppInstance($scopeType, $scopeCode) !== null;
        } catch (\Exception $ex) {
            // Unable to query installed status. Behave as if not installed.
        }

        return false;
    }

    /**
     * Install Zendesk App
     *
     * @param string $scopeType
     * @param int $scopeId
     * @throws LocalizedException
     * @throws \Zendesk\API\Exceptions\AuthException
     */
    public function installZendeskApp(
        $scopeType = \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeId = 0
    ) {
        $api = $this->apiHelper->getZendeskApiInstance($scopeType, $scopeId);

        $api->apps()->install([
            'app_id' => $this->configHelper->getZendeskAppId(),
            'settings' => $this->getZendeskAppSettings($scopeType, $scopeId)
        ]);
    }

    /**
     * Remove Zendesk App
     *
     * @param string $scopeType
     * @param int $scopeId
     * @throws \Zendesk\API\Exceptions\AuthException
     */
    public function removeZendeskApp(
        $scopeType = \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeId = 0
    ) {
        $appId = $this->configHelper->getZendeskAppId();
        $api = $this->apiHelper->getZendeskApiInstance($scopeType, $scopeId);

        $installedApps = $api->apps()->getInstalledApps();

        $installedAppId = null;
        foreach ($installedApps->installations as $installedApp) {
            if ($installedApp->app_id == $appId) {
                $installedAppId = $installedApp->id;
                break;
            }
        }

        if ($installedAppId !== null) {
            $api->apps()->remove($installedAppId);
        }

        // else, app not installed in the first place -- nothing to do.
    }

    /**
     * Update Zendesk app settings in place.
     *
     * @param string $scopeType
     * @param int $scopeId
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\IntegrationException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zendesk\API\Exceptions\AuthException
     */
    public function updateZendeskAppConfiguration(
        $scopeType = \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeId = 0
    ) {
        if (!$this->isZendeskAppInstalled($scopeType, $scopeId)) {
            return; // No app to update -- bail.
        }

        $api = $this->apiHelper->getZendeskApiInstance($scopeType, $scopeId);

        $api->apps()->updateInstallation(
            $this->getZendeskAppInstance($scopeType, $scopeId)->id,
            ['settings' => $this->getZendeskAppSettings($scopeType, $scopeId)]
        );
    }

    /**
     * Get settings for use when installing Zendesk App
     *
     * @param string $scopeType
     * @param int $scopeId
     * @return array
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\IntegrationException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getZendeskAppSettings(
        $scopeType = \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeId = 0
    ) {
        switch ($scopeType) {
            case \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE:
                $storeContext = $this->storeRepository->getById(
                    $this->storeGroupRepository->get(
                        $this->websiteRepository->getById($scopeId)->getDefaultGroupId()
                    )->getDefaultStoreId()
                );
                break;
            case \Magento\Store\Model\ScopeInterface::SCOPE_STORE:
                $storeContext = $this->storeRepository->getById($scopeId);
                break;
            default:
                $storeContext = $this->helper->getDefaultStore();
                break;
        }

        if (!($storeContext instanceof \Magento\Store\Model\Store)) {
            throw new LocalizedException(
                __('Store data object implementation is not compatible with Zendesk integration.')
            );
        }

        return [
            'name' => __('Magento 2 Connector'),

            // API info
            'magentoBaseUrl' => $storeContext->getBaseUrl(),
            'apiToken' => $this->integrationHelper->getAuthToken(),

            // App settings
            'displayName' => $this->configHelper->getZendeskAppDisplayName(),
            'displayOrderStatus' => $this->configHelper->getZendeskAppDisplayOrderStatus(),
            'displayOrderStore' => $this->configHelper->getZendeskAppDisplayOrderStore(),
            'displayItemQuantity' => $this->configHelper->getZendeskAppDisplayItemQuantity(),
            'displayItemPrice' => $this->configHelper->getZendeskAppDisplayItemPrice(),
            'displayTotalPrice' => $this->configHelper->getZendeskAppDisplayTotalPrice(),
            'displayShippingAddress' => $this->configHelper->getZendeskAppDisplayShippingAddress(),
            'displayShippingMethod' => $this->configHelper->getZendeskAppDisplayShippingMethod(),
            'displayTrackingNumber' => $this->configHelper->getZendeskAppDisplayTrackingNumber(),
            'displayOrderComments' => $this->configHelper->getZendeskAppDisplayOrderComments()
        ];
    }
}
