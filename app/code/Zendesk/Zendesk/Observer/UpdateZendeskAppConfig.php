<?php

namespace Zendesk\Zendesk\Observer;

use Zendesk\Zendesk\Helper\ZendeskApp;
use Magento\Framework\Event\Observer;

class UpdateZendeskAppConfig implements \Magento\Framework\Event\ObserverInterface
{
    const CHANGED_PATH_PATTERN = '#^zendesk\/zendesk_app\/display_[a-z_]+$#';
    /**
     * @var ZendeskApp
     */
    protected $zendeskAppHelper;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManger;

    /**
     * UpdateZendeskAppConfig constructor.
     * @param ZendeskApp $zendeskAppHelper
     * @param \Magento\Framework\Message\ManagerInterface $messageManger
     */
    public function __construct(
        \Zendesk\Zendesk\Helper\ZendeskApp $zendeskAppHelper,
        \Magento\Framework\Message\ManagerInterface $messageManger
    ) {
        $this->zendeskAppHelper = $zendeskAppHelper;
        $this->messageManger = $messageManger;
    }

    /**
     * If Zendesk app config settings are changed, update
     * corresponding config of actual app in Zendesk.
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
            $zendeskAppConfigChanged = false;
            foreach ($changedPaths as $changedPath) {
                if (preg_match(self::CHANGED_PATH_PATTERN, $changedPath)) {
                    $zendeskAppConfigChanged = true;
                    break;
                }
            }
        } else {
            // Without changed paths hint, assume app config changes might have happened.
            $zendeskAppConfigChanged = true;
        }

        if (!$zendeskAppConfigChanged) {
            return; // nothing to do here.
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

        try {
            $this->zendeskAppHelper->updateZendeskAppConfiguration($scopeType, $scopeId);
        } catch (\Exception $e) {
            $this->messageManger->addErrorMessage(
                __(
                    'Zendesk app changes detected, but unable to actually ' .
                    'update app configuration in Zendesk account. Error message: "%1".',
                    $e->getMessage()
                )
            );
        }
    }
}
