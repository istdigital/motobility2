<?php

namespace TemplateMonster\Parallax\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

/**
 * Abstract Parallax Block Action.
 */
abstract class Block extends Action
{
    /**
     * Init action metadata.
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    protected function _initAction()
    {
        /** @var \Magento\Backend\Model\View\Result\Forward $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $result
            ->setActiveMenu('TemplateMonster_Parallax::parallax')
            ->addBreadcrumb(__('Parallax Blocks'), __('Parallax Blocks'))
            ->addBreadcrumb(__('Manage Parallax Blocks'), __('Manage Parallax Blocks'))
            ->getConfig()->getTitle()->prepend(__('Parallax Blocks'))
        ;

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('TemplateMonster_Parallax::parallax_block');
    }
}
