<?php
namespace TemplateMonster\SiteMaintenance\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use TemplateMonster\SiteMaintenance\Helper\Data as HelperData;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;
    protected $_helper;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        HelperData $helper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_helper = $helper;

        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();

        $pageTitle = $this->_helper->getTitle();
        $resultPage->getConfig()->getTitle()->set(__($pageTitle));

        return $resultPage;
    }
}
