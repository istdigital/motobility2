<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
namespace Manadev\Core\Controller\Adminhtml\ExtensionControl;

use Magento\Backend\App\AbstractAction;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Manadev\Core\Features;

class Index extends AbstractAction
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var Features
     */
    protected $features;

    public function __construct(Context $context, PageFactory $resultPageFactory, Features $features) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->features = $features;
    }

    public function execute()
    {
        if (count($this->features->getModulesToBeDisabledOrEnabled())) {
            $this->messageManager->addNotice('Please run the following command to actually enable/disable modules: ' .
                '<b><code>php bin/magento mana:update -cd</code></b>');
        }
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Manadev_Core::extension_control');
        $resultPage->getConfig()->getTitle()->prepend((__('Installed MANAdev Extensions')));
        return $resultPage;
    }
}
