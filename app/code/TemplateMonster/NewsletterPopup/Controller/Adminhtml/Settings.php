<?php

namespace TemplateMonster\NewsletterPopup\Controller\Adminhtml;

use Magento\Backend\App\Action;

/**
 * Abstract settings controller.
 *
 * @package TemplateMonster\NewsletterPopup\Controller\Adminhtml
 */
abstract class Settings extends Action
{
    /**
     * @inheritdoc
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('TemplateMonster_NewsletterPopup::newsletter_popup_config');
    }
}