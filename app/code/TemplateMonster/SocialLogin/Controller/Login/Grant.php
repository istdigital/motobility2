<?php

namespace TemplateMonster\SocialLogin\Controller\Login;

use TemplateMonster\SocialLogin\Model\Exception;
use TemplateMonster\SocialLogin\Controller\Login;

/**
 * Grant login action.
 */
class Grant extends Login
{
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $code = $this->getRequest()->getParam('provider');

        $redirect = $this->resultRedirectFactory->create();
        try {
            $provider = $this->_collection->getItemById($code);
            $redirect->setPath($provider->getAuthorizationUrl());
        } catch (Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $redirect->setRefererOrBaseUrl();
        }

        return $redirect;
    }
}
