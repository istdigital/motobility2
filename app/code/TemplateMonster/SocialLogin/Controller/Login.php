<?php

namespace TemplateMonster\SocialLogin\Controller;

use TemplateMonster\SocialLogin\Model\AccountManagement;
use TemplateMonster\SocialLogin\Model\ResourceModel\Provider\Collection as ProviderCollection;
use TemplateMonster\SocialLogin\Helper\Data as SocialLoginHelper;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;

/**
 * Login base controller.
 */
abstract class Login extends Action
{
    /**
     * @var ProviderCollection
     */
    protected $_collection;

    /**
     * @var SocialLoginHelper
     */
    protected $_helper;

    /**
     * @var AccountManagement
     */
    protected $_accountManagement;

    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * Login constructor.
     *
     * @param ProviderCollection $collection
     * @param SocialLoginHelper  $socialLoginHelper
     * @param AccountManagement  $accountManagement
     * @param CustomerSession    $customerSession
     * @param Context            $context
     */
    public function __construct(
        ProviderCollection $collection,
        SocialLoginHelper $socialLoginHelper,
        AccountManagement $accountManagement,
        CustomerSession $customerSession,
        Context $context
    ) {
        $this->_collection = $collection;
        $this->_helper = $socialLoginHelper;
        $this->_accountManagement = $accountManagement;
        $this->_customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc.
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->_helper->isEnabled()) {
            throw new NotFoundException(__('Page not found.'));
        }

        return parent::dispatch($request);
    }
}
