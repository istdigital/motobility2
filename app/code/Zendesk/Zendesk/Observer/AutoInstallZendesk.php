<?php

namespace Zendesk\Zendesk\Observer;

use Magento\Framework\Event\Observer;
use Zendesk\API\Exceptions\AuthException;

class AutoInstallZendesk implements \Magento\Framework\Event\ObserverInterface
{
    const AUTO_INSTALL_FLAG_CONFIG_PATH = 'zendesk/zendesk_integration/auto_install';

    /**
     * @var \Zendesk\Zendesk\Helper\ZendeskApp
     */
    protected $zendeskAppHelper;
    /**
     * @var \Zendesk\Zendesk\Helper\Api
     */
    protected $apiHelper;
    /**
     * @var \Zendesk\Zendesk\Helper\WebWidget
     */
    protected $webWidgetHelper;
    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    protected $storeRepository;
    /**
     * @var \Zendesk\Zendesk\Helper\Config
     */
    protected $configHelper;
    /**
     * @var \Zendesk\Zendesk\Helper\Data
     */
    protected $helper;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManger;
    /**
     * @var \Zendesk\Zendesk\Helper\Integration
     */
    protected $integrationHelper;
    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $configWriter;
    /**
     * @var \Magento\Framework\App\Cache\Manager
     */
    protected $cacheManager;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * AutoInstallZendesk constructor.
     * @param \Zendesk\Zendesk\Helper\Api $apiHelper
     * @param \Zendesk\Zendesk\Helper\ZendeskApp $zendeskAppHelper
     * @param \Zendesk\Zendesk\Helper\WebWidget $webWidgetHelper
     * @param \Zendesk\Zendesk\Helper\Config $configHelper
     * @param \Zendesk\Zendesk\Helper\Integration $integrationHelper
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     * @param \Zendesk\Zendesk\Helper\Data $helper
     * @param \Magento\Framework\Message\ManagerInterface $messageManger
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Framework\App\Cache\Manager $cacheManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Zendesk\Zendesk\Helper\Api $apiHelper,
        \Zendesk\Zendesk\Helper\ZendeskApp $zendeskAppHelper,
        \Zendesk\Zendesk\Helper\WebWidget $webWidgetHelper,
        \Zendesk\Zendesk\Helper\Config $configHelper,
        \Zendesk\Zendesk\Helper\Integration $integrationHelper,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        \Zendesk\Zendesk\Helper\Data $helper,
        \Magento\Framework\Message\ManagerInterface $messageManger,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\App\Cache\Manager $cacheManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->zendeskAppHelper = $zendeskAppHelper;
        $this->apiHelper = $apiHelper;
        $this->webWidgetHelper = $webWidgetHelper;
        $this->storeRepository = $storeRepository;
        $this->configHelper = $configHelper;
        $this->helper = $helper;
        $this->messageManger = $messageManger;
        $this->integrationHelper = $integrationHelper;
        $this->configWriter = $configWriter;
        $this->cacheManager = $cacheManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Install Zendesk app, dealing with any exceptions.
     */
    protected function installZendeskApp()
    {
        try {
            if (!$this->zendeskAppHelper->isZendeskAppInstalled()) {
                $this->zendeskAppHelper->installZendeskApp(
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $this->helper->getDefaultStore()->getId()
                );
            }
        } catch (\Exception $e) {
            return; // Intentionally swallow exception.
        }
    }

    /**
     * Enable web widget at the global scope.
     */
    protected function enableWebWidget()
    {
        // This action is abstracted into its own method
        // for readability, but there's really only one
        // action currently required.

        $this->webWidgetHelper->toggleWebWidget(true);
    }

    /**
     * If only one store or only one brand, auto-map stores to brands.
     *
     * @throws AuthException
     */
    protected function mapBrands()
    {
        $brands = $this->apiHelper->getZendeskApiInstance()->brands()->getBrands()->brands;
        $stores = array_filter(
            $this->storeRepository->getList(),
            function (\Magento\Store\Api\Data\StoreInterface $store) {
                return $store->getId() != 0;
            }
        );

        if (count($stores) < 2 || count($brands) < 2) {
            // Only one store or only one brand -- map all brands to all stores.

            $storeIds = array_map(function (\Magento\Store\Api\Data\StoreInterface $store) {
                return $store->getId();
            }, $stores);

            foreach ($brands as $brand) {
                $this->configHelper->setBrandStores($brand->id, $storeIds);
            }
        }
    }

    /**
     * Auto install Zendesk app and enable web widget
     * when API credentials configured.
     *
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        $website = $observer->getData('website');
        $store = $observer->getData('store');
        $hasChangedPaths = is_array($observer->getData('changed_paths'));
        $changedPaths = $hasChangedPaths ? $observer->getData('changed_paths') : [];

        if ($hasChangedPaths) {
            // If changed paths hint provided, use to determine if API changes have occurred.
            $hasApiCredentialChanges = false;
            foreach (\Zendesk\Zendesk\Helper\Config::API_CREDENTIAL_PATHS as $credentialPath) {
                if (in_array($credentialPath, $changedPaths)) {
                    $hasApiCredentialChanges = true;
                    break;
                }
            }
        } else {
            // If no config path hint, use global flag to determine if auto-install has ever taken place
            // to ensure auto-install only happens the first time the zendesk section is saved.

            $hasApiCredentialChanges = empty($this->scopeConfig->getValue(self::AUTO_INSTALL_FLAG_CONFIG_PATH));
        }

        // First, several guard clause checks

        if (!$hasApiCredentialChanges) {
            return; // Nothing to do -- bail.
        }

        if (!empty($store) || !empty($website)) {
            return; // This change is intended only to take effect globally -- bail.
        }

        try {
            $this->apiHelper->tryAuthenticate();
        } catch (AuthException $e) {
            return; // Invalid API credentials -- unable to proceed.
        }

        // Making it this far means we should proceed with auto configuration.

        $this->integrationHelper->createIntegration();
        $this->messageManger->addSuccessMessage(__('Magento integration configured.'));

        $this->installZendeskApp();
        $this->messageManger->addSuccessMessage(__('Zendesk app installed.'));

        $this->enableWebWidget();
        $this->messageManger->addSuccessMessage(__('Zendesk web widget enabled.'));

        $this->mapBrands();

        // Set global flag to indicate that auto-install has happened
        $this->configWriter->save(self::AUTO_INSTALL_FLAG_CONFIG_PATH, 1);
        $this->cacheManager->clean([\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER]);
    }
}
