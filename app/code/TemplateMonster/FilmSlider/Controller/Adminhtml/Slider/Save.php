<?php
/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FilmSlider\Controller\Adminhtml\Slider;

use Magento\Backend\App\Action;
use TemplateMonster\FilmSlider\Api\SliderRepositoryInterface;
use TemplateMonster\FilmSlider\Api\Data\SliderInterface;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var SliderRepositoryInterface
     */
    protected $_sliderRepository;

    protected $_dataProcessor;

    /**
     * @param Action\Context $context
     */
    public function __construct(Action\Context $context,
                                SliderRepositoryInterface $sliderRepository,
                                PostDataProcessor $dataProcessor
    ) {
        $this->_sliderRepository = $sliderRepository;
        $this->_dataProcessor = $dataProcessor;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('TemplateMonster_FilmSlider::filmslider_save');
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $id = $this->getRequest()->getParam('slider_id');
            if ($id) {
                $model = $this->_sliderRepository->getById($id);
            } else {
                $model = $this->_sliderRepository->getModelInstance();
            }

            $model->addData($data);

            $args = [SliderInterface::NAME,
                SliderInterface::STORE,
                SliderInterface::STATUS,
                SliderInterface::PARAMS,
                'form_key'];

            $dataParams = $this->getRequest()->getPostValue();
            foreach ($args as $arg) {
                if (array_key_exists($arg, $dataParams)) {
                    unset($dataParams[$arg]);
                }
            }

            if ($dataParams) {
                $model->setParams($dataParams);
            }

            $this->_eventManager->dispatch(
                'film_slider_prepare_save',
                ['slider' => $model, 'request' => $this->getRequest()]
            );

            try {
                $model->save();
                $this->messageManager->addSuccess(__('You saved this slider.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['slider_id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/index');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the slider.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['slider_id' => $this->getRequest()->getParam('slider_id')]);
        }
        return $resultRedirect->setPath('*/*/index');
    }
}
