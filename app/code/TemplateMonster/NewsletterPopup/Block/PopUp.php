<?php

namespace TemplateMonster\NewsletterPopup\Block;

use TemplateMonster\NewsletterPopup\Helper\Data as PopupHelper;
use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Newsletter\Model\SubscriberFactory;

/**
 * Newsletter Pop-Up block.
 *
 * @package TemplateMonster\NewsletterPopup\Block
 */
class PopUp extends Template
{
    /**
     * @string
     */
    protected $_template = 'popup.phtml';

    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * @var SubscriberFactory
     */
    protected $_subscriberFactory;

    /**
     * @var PopupHelper
     */
    protected $_helper;

    /**
     * PopUp constructor.
     *
     * @param CustomerSession   $customerSession
     * @param SubscriberFactory $subscriberFactory
     * @param PopupHelper       $helper
     * @param Template\Context  $context
     * @param array             $data
     */
    public function __construct(
        CustomerSession $customerSession,
        SubscriberFactory $subscriberFactory,
        PopupHelper $helper,
        Template\Context $context,
        array $data
    )
    {
        $this->_customerSession = $customerSession;
        $this->_subscriberFactory = $subscriberFactory;
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Check if customer is already subscribed
     *
     * @return bool
     */
    protected function isCustomerAlreadySubscribed()
    {
        if (!$this->_customerSession->isLoggedIn()) {
            return false;
        }

        $customerEmail = $this->_customerSession->getCustomerDataObject()->getEmail();

        /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
        $subscriber = $this->_subscriberFactory->create();
        $subscriber->loadByEmail($customerEmail);

        return $subscriber->isSubscribed();
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        if (!$this->_helper->isEnabled()) {
            return '';
        }
        if ($this->isCustomerAlreadySubscribed()) {
            return '';
        }

        return parent::_toHtml();
    }
}