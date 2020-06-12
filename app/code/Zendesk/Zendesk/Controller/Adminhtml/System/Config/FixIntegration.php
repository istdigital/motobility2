<?php

namespace Zendesk\Zendesk\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\LocalizedException;
use Zendesk\API\Exceptions\AuthException;

class FixIntegration extends \Magento\Backend\App\Action
{
    /**
     * ACL resource ID
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Zendesk_Zendesk::zendesk';

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManger;
    /**
     * @var \Zendesk\Zendesk\Helper\Integration
     */
    protected $integrationHelper;
    /**
     * @var \Zendesk\Zendesk\Helper\ZendeskApp
     */
    protected $zendeskAppHelper;
    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    protected $storeRepository;
    /**
     * @var \Magento\Store\Api\WebsiteRepositoryInterface
     */
    protected $websiteRepository;

    /**
     * FixIntegration constructor.
     * @param Action\Context $context
     * @param \Zendesk\Zendesk\Helper\Integration $integrationHelper
     * @param \Zendesk\Zendesk\Helper\ZendeskApp $zendeskAppHelper
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     * @param \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository
     */
    public function __construct(
        Action\Context $context,
        //end parent parameters
        \Zendesk\Zendesk\Helper\Integration $integrationHelper,
        \Zendesk\Zendesk\Helper\ZendeskApp $zendeskAppHelper,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository
    ) {
        parent::__construct($context);

        $this->messageManger = $context->getMessageManager();
        $this->integrationHelper = $integrationHelper;
        $this->zendeskAppHelper = $zendeskAppHelper;
        $this->storeRepository = $storeRepository;
        $this->websiteRepository = $websiteRepository;
    }

    /**
     * If installed at given scope, remove and reinstall Zendesk app
     *
     * @param string $scopeType
     * @param int $scopeId
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zendesk\API\Exceptions\AuthException
     */
    protected function reinstallZendeskApp(
        $scopeType = \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeId = 0
    ) {
        if ($this->zendeskAppHelper->isZendeskAppInstalled($scopeType, $scopeId)) {
            $this->zendeskAppHelper->removeZendeskApp($scopeType, $scopeId);
            $this->zendeskAppHelper->installZendeskApp($scopeType, $scopeId);
        }
    }

    /**
     * Ensure Zendesk integration is created
     *
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $this->integrationHelper->removeIntegration(); // clean up any existing integration
            $this->integrationHelper->createIntegration(); // create new, activated integration

            $this->reinstallZendeskApp(); // reinstall at default scope
            foreach ($this->websiteRepository->getList() as $website) {
                $this->reinstallZendeskApp(\Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE, $website->getId());
            }
            foreach ($this->storeRepository->getList() as $store) {
                $this->reinstallZendeskApp(\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store->getId());
            }

            $this->messageManager->addSuccessMessage(__('Zendesk integration fixed.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Unable to fix Zendesk integration.'));
        }

        $this->_redirect('adminhtml/system_config/edit', ['section' => 'zendesk']);
    }
}
