<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace TemplateMonster\FilmSlider\Controller\Adminhtml\Slideritem;

use \Magento\Backend\App\Action;
use Magento\Framework\Exception\CouldNotSaveException;

class Edit extends Action
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
        return $this->_authorization->isAllowed('TemplateMonster_FilmSlider::filmslider_item_edit');
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
        $id = $this->getRequest()->getParam('slideritem_id');
        $parentId = $this->getRequest()->getParam('parent_id');

        if (!$id && !$parentId) {
            $this->messageManager->addError(__('Can not add slide.'));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/');
        }

        $sliderItemRepository = $this->_objectManager->create('TemplateMonster\FilmSlider\Api\SliderItemRepositoryInterface');
        $sliderRepository = $this->_objectManager->create('TemplateMonster\FilmSlider\Api\SliderRepositoryInterface');

        if ($id) {
            try {
                $model = $sliderItemRepository->getById($id);
            } catch (CouldNotSaveException $e) {
                $this->messageManager->addError(__('This slider no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError(__('This slider no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        } else {
            $model = $sliderItemRepository->getModelInstance();
        }


        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        if (!$model->getParentId() && $parentId) {
            $model->setParentId($parentId);
        }

        $this->_coreRegistry->register(\TemplateMonster\FilmSlider\Model\SliderItem::REGISTRY_NAME, $model);

        // Need to recover slider model
        try {
            $sliderModel = $sliderRepository->getById($parentId ? $parentId  : $model->getParentId());
            if ($sliderModel && $sliderModel->getData()) {
                $this->_coreRegistry->register(\TemplateMonster\FilmSlider\Model\Slider::REGISTRY_NAME, $sliderModel);
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Can not add slide to slide.'));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/');
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Slider Item') : __('Slider Item'),
            $id ? __('Slider Item') : __('Slider Item')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Slider Item'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getTitle() : __('New Slider Item'));

        return $resultPage;
    }
}
