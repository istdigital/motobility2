<?php

namespace TemplateMonster\SocialLogin\Controller\Login;

use TemplateMonster\SocialLogin\Model\Exception;
use TemplateMonster\SocialLogin\Controller\Login;

/**
 * Connect login action.
 */
class Connect extends Login
{
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     *
     * @throws \TemplateMonster\SocialLogin\Model\Exception
     */
    public function execute()
    {
        $code = $this->getRequest()->getParam('provider');

        try {
            $provider = $this->_collection->getItemById($code);

            $token = $provider->getAccessToken();
            $data = $provider->getUserData($token);

            $customer = $this->_accountManagement->authenticateByOAuth($data);
            $this->_customerSession->setCustomerDataAsLoggedIn($customer);
            $this->_customerSession->regenerateId();

            $this->messageManager->addSuccess(__('You have been successfully logged in.'));
        } catch (Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        } catch (\Exception $e) {
            $this->messageManager->addError(__('There is an error occurred while trying to login.'));
        }

        if (!$this->_response->isRedirect()) {
            $redirect = $this->resultRedirectFactory->create();
            $redirect->setPath('/');

            return $redirect;
        }
    }
}
