<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Zemez\Repayment\Helper;

/**
 * Zemez base helper
 *
 * @deprecated 100.2.0
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const USER_EMAIL_TEMPLATE = 'repayment/email/user_email_template';

    const USER_EMAIL_SENDER = 'repayment/email/user_email_sender';

    const ADMIN_EMAIL_TEMPLATE = 'repayment/email/admin_email_template';

    const ADMIN_EMAIL_RECEIVER = 'repayment/email/admin_email_receiver';


    public function getUserEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            self::USER_EMAIL_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

    }

    public function getUserEmailSender()
    {
        $r = $this->scopeConfig->getValue(
            self::USER_EMAIL_SENDER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $this->scopeConfig->getValue(
            "trans_email/ident_$r/email",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

    }
    
    public function getAdminEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            self::ADMIN_EMAIL_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

    }

    public function getAdminEmailReceiver()
    {
        $r = $this->scopeConfig->getValue(
            self::ADMIN_EMAIL_RECEIVER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $this->scopeConfig->getValue(
            "trans_email/ident_$r/email",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

}
