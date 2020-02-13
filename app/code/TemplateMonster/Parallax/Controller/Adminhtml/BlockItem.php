<?php

namespace TemplateMonster\Parallax\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

/**
 * Abstract Parallax Item Action.
 */
abstract class BlockItem extends Action
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
            ->addBreadcrumb(__('Parallax Items'), __('Parallax Items'))
            ->addBreadcrumb(__('Manage Parallax Items'), __('Manage Parallax Items'))
            ->getConfig()->getTitle()->prepend(__('Parallax Block Items'))
        ;

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('TemplateMonster_Parallax::parallax_item');
    }
}
