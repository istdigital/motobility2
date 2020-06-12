<?php

namespace Zendesk\Zendesk\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;

class RemoveIntegration extends \Magento\Backend\App\Action
{
    /**
     * ACL resource ID
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Zendesk_Zendesk::zendesk';
    /**
     * @var \Zendesk\Zendesk\Helper\ZendeskApp
     */
    protected $zendeskAppHelper;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManger;
    /**
     * @var \Zendesk\Zendesk\Helper\Integration
     */
    protected $integrationHelper;
    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    protected $storeRepository;
    /**
     * @var \Zendesk\Zendesk\Helper\WebWidget
     */
    protected $webWidgetHelper;
    /**
     * @var \Magento\Store\Api\WebsiteRepositoryInterface
     */
    protected $websiteRepository;
    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $configWriter;
    /**
     * @var \Magento\Framework\App\Cache\Manager
     */
    protected $cacheManager;

    /**
     * RemoveIntegration constructor.
     * @param Action\Context $context
     * @param \Zendesk\Zendesk\Helper\ZendeskApp $zendeskAppHelper
     * @param \Zendesk\Zendesk\Helper\Integration $integrationHelper
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     * @param \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository
     * @param \Zendesk\Zendesk\Helper\WebWidget $webWidgetHelper
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Framework\App\Cache\Manager $cacheManager
     */
    public function __construct(
        Action\Context $context,
        //end parent parameters
        \Zendesk\Zendesk\Helper\ZendeskApp $zendeskAppHelper,
        \Zendesk\Zendesk\Helper\Integration $integrationHelper,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository,
        \Zendesk\Zendesk\Helper\WebWidget $webWidgetHelper,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\App\Cache\Manager $cacheManager
    ) {
        parent::__construct($context);

        $this->zendeskAppHelper = $zendeskAppHelper;
        $this->messageManger = $context->getMessageManager();
        $this->integrationHelper = $integrationHelper;
        $this->storeRepository = $storeRepository;
        $this->webWidgetHelper = $webWidgetHelper;
        $this->websiteRepository = $websiteRepository;
        $this->configWriter = $configWriter;
        $this->cacheManager = $cacheManager;
    }

    /**
     * Install / uninstall Zendesk app
     *
     * {@inheritdoc}
     */
    public function execute()
    {

        $stores = $this->storeRepository->getList();
        $websites = $this->websiteRepository->getList();

        try {
            // Remove Magento Integration
            $this->integrationHelper->removeIntegration();
            $this->messageManager->addSuccessMessage(__('Zendesk integration successfully removed.'));
        } catch (\Exception $e) {
            $this->messageManger->addErrorMessage(__(
                'Zendesk integration not removed: %1',
                $e->getMessage()
            ));
        }

        // Remove Zendesk App
        try {
            $this->zendeskAppHelper->removeZendeskApp(); // Remove at default scope
            foreach ($websites as $website) {
                $this->zendeskAppHelper->removeZendeskApp(
                    \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
                    $website->getId()
                );
            }
            foreach ($stores as $store) {
                $this->zendeskAppHelper->removeZendeskApp(
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $store->getId()
                );
            }
            $this->messageManager->addSuccessMessage(__('Zendesk App successfully removed.'));
        } catch (\Exception $e) {
            $this->messageManger->addErrorMessage(__(
                'Zendesk App not removed: %1',
                $e->getMessage()
            ));
        }

        // Remove web widget
        $this->webWidgetHelper->toggleWebWidget(false); // Remove at default scope
        foreach ($websites as $website) {
            $this->webWidgetHelper->toggleWebWidget(
                false,
                \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
                $website->getId()
            );
        }
        foreach ($stores as $store) {
            $this->webWidgetHelper->toggleWebWidget(
                false,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store->getId()
            );
        }
        $this->messageManager->addSuccessMessage(__('Zendesk Web Widget disabled.'));

        // Clear API credentials
        foreach (\Zendesk\Zendesk\Helper\Config::API_CREDENTIAL_PATHS as $configPath) {
            $this->configWriter->delete($configPath); // Clear default scope
            foreach ($websites as $website) {
                $this->configWriter->delete(
                    $configPath,
                    \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
                    $website->getId()
                );
            }
            foreach ($stores as $store) {
                $this->configWriter->delete(
                    $configPath,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $store->getId()
                );
            }
        }
        // Newly set config value won't take effect unless config cache is cleaned.
        $this->cacheManager->clean([\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER]);
        $this->messageManager->addSuccessMessage(__('Zendesk API credentials cleared.'));

        // Done!

        $this->_redirect('adminhtml/system_config/edit', ['section' => 'zendesk']);
    }
}
