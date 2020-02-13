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

class Validate extends \Magento\Backend\App\Action
{

    /**
     * @var SliderRepositoryInterface
     */
    protected $_sliderRepository;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $_resultFactory;

    public function __construct(Action\Context $context,
                                SliderRepositoryInterface $sliderRepository)
    {
        $this->_sliderRepository = $sliderRepository;
        $this->_resultFactory = $context->getResultFactory();
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
        $jsonResponse = $this->_resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        $sliderId = $this->getRequest()->getParam('slider_id');
        $startSlide = $this->getRequest()->getParam('startSlide');

        /**
         * Do not need validation.
         */
        if (!$sliderId || !($startSlide >= 0)) {
            $jsonResponse->setData(['message'=>__("Ok")]);
            return $jsonResponse;
        }

        try {
            $model = $this->_sliderRepository->getById($sliderId);
            $activeSlidesCountResult = $model->getActiveSlidesCount();
            $activeSlidesCount = $activeSlidesCountResult['slider_items'];
        } catch (\Exception $e) {
            $jsonResponse->setData(['message'=>__("Slider does not exist!"), 'error'=>"slider_error"]);
            return $jsonResponse;
        }


        if ($activeSlidesCount == 0) {
            $jsonResponse->setData(['message'=>__("Current slider does not have active slides.
            Please leave field (Slider settings > Start Slider) empty or add slides."),
                'error'=>"slider_error"]);
            return $jsonResponse;
        }

        --$activeSlidesCount;
        if ($startSlide > $activeSlidesCount) {
            $jsonResponse->setData(['message'=>__("You can not specify (Slider settings > Start Slider) field more than: ".$activeSlidesCount), 'error'=>"slider_error"]);
            return $jsonResponse;
        }

        $jsonResponse->setData(['message'=>__("Ok")]);
        return $jsonResponse;
    }
}
