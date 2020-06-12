<?php

namespace Zendesk\Zendesk\Controller\Adminhtml\System;

class ConfigRedirect extends \Magento\Backend\App\Action
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        return $this->_redirect('adminhtml/system_config/edit', ['section' => 'zendesk']);
    }
}
