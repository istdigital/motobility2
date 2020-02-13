<?php

namespace TemplateMonster\ShopByBrand\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;

/**
 * New brand action.
 */
class NewAction extends Action
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
