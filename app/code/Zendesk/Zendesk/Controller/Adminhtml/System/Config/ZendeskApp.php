<?php

namespace Zendesk\Zendesk\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;

class ZendeskApp extends \Magento\Backend\App\Action
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
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * ZendeskApp constructor.
     * @param Action\Context $context
     * @param \Zendesk\Zendesk\Helper\ZendeskApp $zendeskAppHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Action\Context $context,
        //end parent parameters
        \Zendesk\Zendesk\Helper\ZendeskApp $zendeskAppHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);

        $this->zendeskAppHelper = $zendeskAppHelper;
        $this->messageManger = $context->getMessageManager();
        $this->storeManager = $storeManager;
    }

    /**
     * Install / uninstall Zendesk app
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $install = (bool)$this->getRequest()->getParam('install');
        $storeId = (int)$this->getRequest()->getParam('store_id');

        $store = $this->storeManager->getStore($storeId); // load store to ensure it's a valid ID

        if ($install) {
            try {
                $this->zendeskAppHelper->installZendeskApp(
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $store->getId()
                );

                $this->messageManager->addSuccessMessage(__('Zendesk App Installed.'));
            } catch (\Exception $ex) {
                $this->messageManager->addErrorMessage(__('Unable to install Zendesk App: "%1".', $ex->getMessage()));
            }
        } else {
            try {
                $this->zendeskAppHelper->removeZendeskApp(\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);

                $this->messageManager->addSuccessMessage(__('Zendesk App Uninstalled.'));
            } catch (\Exception $ex) {
                $this->messageManager->addErrorMessage(__('Unable to uninstall Zendesk App: "%1".', $ex->getMessage()));
            }
        }

        $this->_redirect('adminhtml/system_config/edit', ['section' => 'zendesk']);
    }
}
