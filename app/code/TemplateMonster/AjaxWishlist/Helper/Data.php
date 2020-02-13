<?php

namespace TemplateMonster\AjaxWishlist\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Data Helper
 *
 * @package TemplateMonster\AjaxWishlist\Helper
 */
class Data extends AbstractHelper
{
    /**
     * @var string
     */
    const XML_PATH_ENABLED = 'ajaxwishlist/general/enabled';

    /**
     * @var string
     */
    const XML_PATH_SHOW_SPINNER = 'ajaxwishlist/general/show_spinner';

    /**
     * @var string
     */
    const XML_PATH_SHOW_SUCCESS_MESSAGE = 'ajaxwishlist/general/show_success_message';

    /**
     * @var string
     */
    const XML_PATH_SUCCESS_MESSAGE_TEXT = 'ajaxwishlist/general/success_message_text';

    /**
     * Check is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check is show spinner.
     *
     * @return bool
     */
    public function isShowSpinner()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SHOW_SPINNER,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check is show success message.
     *
     * @return bool
     */
    public function isShowSuccessMessage()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SHOW_SUCCESS_MESSAGE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get success message text config value.
     *
     * @return string
     */
    public function getSuccessMessageText()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SUCCESS_MESSAGE_TEXT,
            ScopeInterface::SCOPE_STORE
        );
    }
}