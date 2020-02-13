<?php

namespace TemplateMonster\Parallax\Controller\Adminhtml\Block;

use TemplateMonster\Parallax\Controller\Adminhtml\Block;
use Magento\Framework\Controller\ResultFactory;

/**
 * New block action.
 */
class NewAction extends Block
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
