<?php

namespace TemplateMonster\SiteMaintenance\Controller\Index;

use Magento\Newsletter\Controller\Subscriber\NewAction as CoreNewAction;

class NewAction extends CoreNewAction
{
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        parent::execute();

        return $resultRedirect;
    }
}
