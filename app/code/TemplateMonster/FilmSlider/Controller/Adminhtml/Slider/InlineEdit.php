<?php
/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FilmSlider\Controller\Adminhtml\Slider;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use TemplateMonster\FilmSlider\Api\SliderRepositoryInterface;
use Magento\Framework\Controller\Result\JsonFactory;

class InlineEdit extends Action
{

    /**
     * @var SliderRepositoryInterface
     */
    protected $sliderRepository;

    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * InlineEdit constructor.
     * @param Context $context
     * @param SliderRepositoryInterface $sliderRepository
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        SliderRepositoryInterface $sliderRepository,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->sliderRepository = $sliderRepository;
        $this->jsonFactory = $jsonFactory;
    }
    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('TemplateMonster_FilmSlider::filmslider_mass');
    }


    /**
     * @return $this
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        foreach (array_keys($postItems) as $sliderId) {
            $slider = $this->sliderRepository->getById($sliderId);
            try {
                $sliderData = $postItems[$sliderId];
                $slider->setData($sliderData);
                $this->sliderRepository->save($slider);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages[] = $this->getErrorWithSlider($slider, $e->getMessage());
                $error = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorWithSlider($slider, $e->getMessage());
                $error = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithSlider(
                    $slider,
                    __('Something went wrong while saving the slider.')
                );
                $error = true;
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * @param $slider
     * @param $errorText
     * @return string
     */
    protected function getErrorWithSlider($slider, $errorText)
    {
        return '[Slider ID: ' . $slider->getId() . '] ' . $errorText;
    }
}
