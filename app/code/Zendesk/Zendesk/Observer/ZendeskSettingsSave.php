<?php

namespace Zendesk\Zendesk\Observer;

use Magento\Framework\Event\Observer;
use Zendesk\API\Exceptions\AuthException;

class ZendeskSettingsSave implements \Magento\Framework\Event\ObserverInterface
{
    const ZENDESK_CONFIG_SECTION_NAME = 'zendesk';

    /**
     * @var \Zendesk\Zendesk\Helper\Api
     */
    protected $apiHelper;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    /**
     * @var \Zendesk\Zendesk\Helper\Config
     */
    protected $configHelper;

    /**
     * ZendeskSettingsSave constructor.
     * @param \Zendesk\Zendesk\Helper\Config $configHelper
     * @param \Zendesk\Zendesk\Helper\Api $apiHelper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Zendesk\Zendesk\Helper\Config $configHelper,
        \Zendesk\Zendesk\Helper\Api $apiHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->apiHelper = $apiHelper;
        $this->messageManager = $messageManager;
        $this->configHelper = $configHelper;
    }

    /**
     * If saving Zendesk section and authentication values have been provided,
     * validate and show error message if they are invalid.
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
            // With changed paths hint, look for specific fields to have been changed.
            $hasApiCredentialChanges = false;
            foreach (\Zendesk\Zendesk\Helper\Config::API_CREDENTIAL_PATHS as $apiCredentialPath) {
                if (in_array($apiCredentialPath, $changedPaths)) {
                    $hasApiCredentialChanges = true;
                    break;
                }
            }
        } else {
            // Without changed paths hint, assume API credential changes might have happened.
            $hasApiCredentialChanges = true;
        }

        if (!$hasApiCredentialChanges) {
            return; // No API credential config changes -- bail.
        }

        // Determine scope type and code from presence or absence of $website or $store
        $scopeType = \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        $scopeId = 0;

        if (!empty($website)) {
            $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE;
            $scopeId = $website;
        }
        if (!empty($store)) {
            $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $scopeId = $store;
        }

        if (empty($this->configHelper->getDomain($scopeType, $scopeId))) {
            return;
        }
        if (empty($this->configHelper->getAgentEmail($scopeType, $scopeId))) {
            return;
        }
        if (empty($this->configHelper->getApiToken($scopeType, $scopeId))) {
            return;
        }

        try {
            $this->apiHelper->tryAuthenticate($scopeType, $scopeId);
        } catch (AuthException $e) {
            $this->messageManager->addErrorMessage(__('Unable to authenticate Zendesk credentials.'));
        }
    }
}
