<?php

namespace TemplateMonster\ThemeOptions\Controller\Adminhtml\Settings;

use TemplateMonster\ThemeOptions\Model\ResourceModel\Config\Data as ConfigDataResource;
use TemplateMonster\ThemeOptions\Controller\Adminhtml\Settings;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Backend\App\Action\Context;

/**
 * Settings reset action.
 *
 * @package TemplateMonster\ThemeOptions\Controller\Adminhtml\Settings
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
                $this->messageManager->addSuccess(__('Settings have been successfully reset.'));
            }
            catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        else {
            $this->messageManager->addError(__('Invalid config section provided.'));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setRefererUrl();

        return $resultRedirect;
    }
}