<?php
/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FilmSlider\Controller\Adminhtml\Slideritem;

use Magento\Backend\App\Action;
use TemplateMonster\FilmSlider\Api\SliderItemRepositoryInterface;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var SliderRepositoryInterface
     */
    protected $_sliderItemRepository;

    /**
     * @param Action\Context $context
     */
    public function __construct(Action\Context $context,
                                SliderItemRepositoryInterface $sliderRepository
    ) {
        $this->_sliderItemRepository = $sliderRepository;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('TemplateMonster_FilmSlider::filmslider_item_save');
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $id = $this->getRequest()->getParam('slideritem_id');
            if ($id) {
                $model = $this->_sliderItemRepository->getById($id);
            } else {
                $model = $this->_sliderItemRepository->getModelInstance();
            }

            $model->setData($data);

            $this->_eventManager->dispatch(
                'film_slider_item_prepare_save',
                ['sliderItem' => $model, 'request' => $this->getRequest()]
            );
            try {
                $model->save();
                $this->messageManager->addSuccess(__('You saved this slider Item.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['slideritem_id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/slider/edit', ['slider_id'=>$model->getParentId()]);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the slider item.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['slider_id' => $this->getRequest()->getParam('slideritem_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
