<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Zemez\StockAlert\Helper;

/**
 * Zemez base helper
 *
 * @deprecated 100.2.0
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_POPUPTEXT = 'stockalert/frontend/popup_text';

    const XML_PATH_NEWSLETTERTEXT = 'stockalert/frontend/newsletter_text';

    const XML_PATH_THANKYOUTEXT = 'stockalert/frontend/thankyou_text';

    const XML_PATH_RECEIVEREMAIL = 'stockalert/email/receiver_email_identity';

    const XML_PATH_SENDEREMAIL = 'stockalert/email/sender_email_identity';

    const XML_PATH_ADMINTEMPLATE = 'stockalert/email/admin_email_template';

    const XML_PATH_USERTEMPLATE = 'stockalert/email/user_email_template';

    public function getPopupText()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_POPUPTEXT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

    }
    public function getNewsletterText()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_NEWSLETTERTEXT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getThankyouText()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_THANKYOUTEXT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function emailRecipient()
    {
        $r = $this->scopeConfig->getValue(
            self::XML_PATH_RECEIVEREMAIL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $this->scopeConfig->getValue(
            "trans_email/ident_$r/email",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function emailSenderMail()
    {
        $r = $this->scopeConfig->getValue(
            self::XML_PATH_SENDEREMAIL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $this->scopeConfig->getValue(
            "trans_email/ident_$r/email",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function emailSenderName()
    {
        $r = $this->scopeConfig->getValue(
            self::XML_PATH_SENDEREMAIL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $this->scopeConfig->getValue(
            "trans_email/ident_$r/name",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function emailSender()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SENDEREMAIL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getAdminEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ADMINTEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getUserEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_USERTEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }


}
