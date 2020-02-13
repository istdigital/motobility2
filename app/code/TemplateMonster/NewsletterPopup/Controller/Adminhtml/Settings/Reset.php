<?php

namespace TemplateMonster\NewsletterPopup\Controller\Adminhtml\Settings;

use TemplateMonster\NewsletterPopup\Model\ResourceModel\Config\Data as ConfigDataResource;
use TemplateMonster\NewsletterPopup\Controller\Adminhtml\Settings;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Backend\App\Action\Context;

/**
 * Settings reset action.
 *
 * @package TemplateMonster\NewsletterPopup\Controller\Adminhtml\Settings
 */
class Reset extends Settings
{
    /**
     * @var ConfigDataResource
     */
    protected $_configDataResource;

    /**
     * @var ReinitableConfigInterface
     */
    protected $_config;

    /**
     * Reset constructor.
     *
     * @param Context                   $context
     * @param ConfigDataResource        $configDataResource
     * @param ReinitableConfigInterface $config
     */
    public function __construct(
        Context $context,
        ConfigDataResource $configDataResource,
        ReinitableConfigInterface $config
    )
    {
        $this->_configDataResource = $configDataResource;
        $this->_config = $config;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        if ($section = $this->getRequest()->getParam('section')) {
            try {
                $this->_configDataResource->clearSectionData($section);
                $this->_config->reinit();
                $this->messageManager->addSuccessMessage(__('Settings have been successfully reset.'));
            }
            catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        else {
            $this->messageManager->addErrorMessage(__('Invalid config section provided.'));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setRefererUrl();

        return $resultRedirect;
    }

    /**
     * @inheritdoc
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('TemplateMonster_NewsletterPopup::newsletter_popup_reset');
    }
}