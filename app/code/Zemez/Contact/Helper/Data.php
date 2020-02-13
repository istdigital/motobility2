<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Zemez\Contact\Helper;

/**
 * Zemez base helper
 *
 * @deprecated 100.2.0
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_SITEKEY = 'contact/captcha/sitekey';

    const XML_PATH_SERVERKEY = 'contact/captcha/serverkey';

    const XML_PATH_EMAIL_TEMPLATE = 'contact/email/thankyou_email_template';

    /**
     * Get site key
     *
     * @return string
     */
    public function getSiteKey()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SITEKEY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

    }

    /**
     * Get site key
     *
     * @return string
     */
    public function getServerKey()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SERVERKEY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * {@inheritdoc}
     */
    public function userEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

}
