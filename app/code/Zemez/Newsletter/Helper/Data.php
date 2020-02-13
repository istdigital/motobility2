<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Zemez\Newsletter\Helper;

/**
 * Zemez base helper
 *
 * @deprecated 100.2.0
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_ADMINSENDER = 'newsletter/subscription/admin_email_sender';

    const XML_PATH_ADMINRECEIVER = 'newsletter/subscription/admin_email_receiver';

    const XML_PATH_ADMIN_EMAIL_TEMPLATE = 'newsletter/subscription/admin_email_template';

    public function emailSender()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ADMINSENDER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function emailRecipientMail()
    {
        $r = $this->scopeConfig->getValue(
            self::XML_PATH_ADMINRECEIVER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $this->scopeConfig->getValue(
            "trans_email/ident_$r/email",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function adminEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ADMIN_EMAIL_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

}
