<?php
/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FilmSlider\Controller\Adminhtml\Slideritem;

use \Magento\Backend\App\Action;
use Magento\Framework\Exception\CouldNotDeleteException;

class Delete  extends Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('TemplateMonster_FilmSlider::filmslider_item_delete');
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('TemplateMonster_FilmSlider::film_slider')
            ->addBreadcrumb(__('Film Slider'), __('Film Slider'))
            ->addBreadcrumb(__('Manage Slider'), __('Manage Slider'));
        return $resultPage;
    }

    /**
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {

        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('slideritem_id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            $title = "";
            try {
                // init model and delete
                $sliderItemRepo =
                    $this->_objectManager->create('TemplateMonster\FilmSlider\Api\SliderItemRepositoryInterface');
                $model = $sliderItemRepo->getById($id);
                $title = $model->getTitle();
                $sliderItemRepo->deleteById($model->getId());
                // display success message
                $this->messageManager->addSuccess(__('The slider item has been deleted.'));
                // go to grid
                $this->_eventManager->dispatch(
                    'adminhtml_slider_item_on_delete',
                    ['title' => $title, 'status' => 'success']
                );

                return $resultRedirect->setPath('*/slider/edit', ['slider_id'=>$model->getParentId()]);
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_slider_item_on_delete',
                    ['title' => $title, 'status' => 'fail']
                );
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['slideritem_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a slider item to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
