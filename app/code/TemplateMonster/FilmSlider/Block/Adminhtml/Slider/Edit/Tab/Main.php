<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FilmSlider\Block\Adminhtml\Slider\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Main extends Generic implements TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareLayout()
    {
        \Magento\Framework\Data\Form::setFieldsetRenderer(
            $this->getLayout()->createBlock(
                'TemplateMonster\FilmSlider\Block\Adminhtml\Renderer\FieldsetAccordion',
                $this->getNameInLayout() . '_fieldset'
            )
        );
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('film_slider');

        /*
         * Checking if user have permissions to save information
         */
        if ($this->_isAllowedAction('TemplateMonster_FilmSlider::filmslider_save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('slider_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Main settings')]);

        if ($model->getId()) {
            $fieldset->addField('slider_id', 'hidden', ['name' => 'slider_id']);
        }

        $fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Slider Title'),
                'title' => __('Slider Title'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

        /**
         * Check is single store mode
         */
        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'store_id',
                'multiselect',
                [
                    'name' => 'stores[]',
                    'label' => __('Store View'),
                    'title' => __('Store View'),
                    'required' => true,
                    'values' => $this->_systemStore->getStoreValuesForForm(false, true),
                    'disabled' => $isElementDisabled
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $field->setRenderer($renderer);
        } else {
            $fieldset->addField(
                'store_id',
                'hidden',
                ['name' => 'stores[]', 'value' => $this->_storeManager->getStore(true)->getId()]
            );
            $model->setStoreId($this->_storeManager->getStore(true)->getId());
        }

        $fieldset->addField(
            'params',
            'hidden',
            [
                'name' => 'params',
                'label' => __('Params'),
                'title' => __('Params'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'status',
                'required' => true,
                'values' => [
                    true =>__('Enable'),
                    false =>__('Disable')
                ],
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'width',
            'text',
            [
                'name' => 'width',
                'label' => __('Slide width'),
                'title' => __('Slide width'),
                'class' => 'validate-int-or-percentage',
                'required' => true,
                'after_element_html' => __('Sets slide width.
                Can be set to a fixed value, like 900 (indicating 900 pixels), or to a percentage value,
                like \'100%\'. It\'s important to note that percentage values need to be specified inside quotes.
                For fixed values, the quotes are not necessary.'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'height',
            'text',
            [
                'name' => 'height',
                'label' => __('Slide height'),
                'title' => __('Slide height'),
                'required' => true,
                'class' => '',
                'after_element_html' => __('Sets slide height.
                Can be set to a fixed value, like 500 (indicating 500 pixels), or to a percentage value,
                like \'100%\'. It\'s important to note that percentage values need to be specified inside quotes.
                For fixed values, the quotes are not necessary.'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldsetImgSettings = $form->addFieldset('img_settings_fieldset',
            ['legend' => __('Image settings')]);

        $fieldsetImgSettings->addField(
            'aspectRatio',
            'text',
            [
                'name' => 'aspectRatio',
                'label' => __('The aspect ratio'),
                'class' => 'validate-aspect-ratio',
                'title' => __('The aspect ratio'),
                'required' => true,
                'after_element_html' => __('Sets the aspect ratio of the slides.'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldsetImgSettings->addField(
            'imageScaleMode',
            'select',
            [
                'name' => 'imageScaleMode',
                'label' => __('Image Scale Mode'),
                'title' => __('Image Scale Mode'),
                'values' => [
                    'cover'   => __('Cover'),
                    'contain' => __('Contain'),
                    'exact' => __('Exact'),
                    'none' => __('None'),
                ],
                'after_element_html' => __('Sets the scale mode of the main slide images.'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldsetImgSettings->addField(
            'allowScaleUp',
            'select',
            [
                'name' => 'allowScaleUp',
                'label' => __('Allow Scale Up'),
                'title' => __('Allow Scale Up'),
                'values' => [
                    'true' =>__('Enable'),
                    'false' =>__('Disable')
                ],
                'after_element_html' => __('Indicates if the image can be scaled up more
                                            than its original size.'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldsetImgSettings->addField(
            'autoHeight',
            'select',
            [
                'name' => 'autoHeight',
                'label' => __('Auto Height'),
                'title' => __('Auto Height'),
                'values' => [
                    'false' =>__('Disable'),
                    'true' =>__('Enable'),
                ],
                'after_element_html' => __('Indicates if height of the slider will
                                            be adjusted to the height of the selected slide.'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldsetImgSettings->addField(
            'centerImage',
            'select',
            [
                'name' => 'centerImage',
                'label' => __('Center Image'),
                'title' => __('Center Image'),
                'values' => [
                    'true' =>__('Enable'),
                    'false' =>__('Disable')
                ],
                'after_element_html' => __('Indicates if the image will be centered.'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldsetImgSettings->addField(
            'orientation',
            'select',
            [
                'name' => 'orientation',
                'label' => __('Orientation'),
                'title' => __('Orientation'),
                'values' => [
                    'horizontal' => __('Horizontal'),
                    'vertical' => __('Vertical'),
                ],
                'after_element_html' => __('Indicates whether the slides will be arranged horizontally
                                            or vertically.'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldsetImgSettings->addField(
            'forceSize',
            'select',
            [
                'name' => 'forceSize',
                'label' => __('Force Size'),
                'title' => __('Force Size'),
                'values' => [
                    'none' => __('None'),
                    'fullWidth' => __('fullWidth'),
                    'fullWindow' => __('fullWindow'),
                ],
                'after_element_html' => __('Indicates if the size of the slider will be forced
                to full width or full window.'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldsetSlidesSettings = $form->addFieldset('slides_settings_fieldset',
            ['legend' => __('Slides settings')]);

        $fieldsetSlidesSettings->addField(
            'slideAnimationDuration',
            'text',
            [
                'name' => 'slideAnimationDuration',
                'label' => __('Slide Animation Duration'),
                'title' => __('Slide Animation Duration'),
                'class' => 'validate-not-negative-number',
                'after_element_html' => __('Sets the duration of the slide animation.'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldsetSlidesSettings->addField(
            'heightAnimationDuration',
            'text',
            [
                'name' => 'heightAnimationDuration',
                'label' => __('Height Animation Duration'),
                'title' => __('Height Animation Duration'),
                'class' => 'validate-not-negative-number',
                'after_element_html' => __('Sets the duration of the height animation.'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldsetSlidesSettings->addField(
            'visibleSize',
            'text',
            [
                'name' => 'visibleSize',
                'label' => __('Visible Size'),
                'title' => __('Visible Size'),
                'class' => 'validate-visible-size',
                'after_element_html' => __('Sets the size of the visible area, allowing for more slides to become
                                            visible near the selected slide.'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldsetSlidesSettings->addField(
            'startSlide',
            'text',
            [
                'name' => 'startSlide',
                'label' => __('Start Slide'),
                'title' => __('Start Slide'),
                'class' => 'validate-not-negative-number validate-number-range number-range-0-30',
                'after_element_html' => __('Sets the slide that will be selected when the slider
                                            loads.'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldsetSlidesSettings->addField(
            'shuffle',
            'select',
            [
                'name' => 'shuffle',
                'label' => __('Shuffle'),
                'title' => __('Shuffle'),
                'values' => [
                    'false' =>__('Disable'),
                    'true' =>__('Enable'),
                ],
                'after_element_html' => __('Indicates if the slides will be shuffled.'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldsetSlidesSettings->addField(
            'loop',
            'select',
            [
                'name' => 'loop',
                'label' => __('Loop'),
                'title' => __('Loop'),
                'values' => [
                    'true' =>__('Enable'),
                    'false' =>__('Disable')
                ],
                'after_element_html' => __('Indicates if the slider will be loopable (infinite scrolling).'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldsetFadeAnimationSettings = $form->addFieldset('fade_animation_settings_fieldset',
            ['legend' => __('Fade Animation settings')]);

        $fieldsetFadeAnimationSettings->addField(
            'fade',
            'select',
            [
                'name' => 'fade',
                'label' => __('Fade'),
                'title' => __('Fade'),
                'values' => [
                    'false' =>__('Disable'),
                    'true' =>__('Enable'),
                ],
                'after_element_html' => __('Indicates if fade will be used.'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldsetFadeAnimationSettings->addField(
            'fadeOutPreviousSlide',
            'select',
            [
                'name' => 'fadeOutPreviousSlide',
                'label' => __('Fade Out Previous Slide'),
                'title' => __('Fade Out Previous Slide'),
                'values' => [
                    'true' =>__('Enable'),
                    'false' =>__('Disable')
                ],
                'after_element_html' => __('Indicates if the previous slide will be faded out
                                            (in addition to the next slide being faded in).'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldsetFadeAnimationSettings->addField(
            'fadeDuration',
            'text',
            [
                'name' => 'fadeDuration',
                'label' => __('Fade Duration'),
                'title' => __('Fade Duration'),
                'class' => 'validate-not-negative-number',
                'after_element_html' => __('Sets the duration of the fade effect. In miliseconds'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldsetAutoPlaySettings = $form->addFieldset('autoplay_settings_fieldset',
            ['legend' => __('Auto Play settings')]);

        $fieldsetAutoPlaySettings->addField(
            'autoplay',
            'select',
            [
                'name' => 'autoplay',
                'label' => __('Auto Play'),
                'title' => __('Auto Play'),
                'values' => [
                    'true' =>__('Enable'),
                    'false' =>__('Disable')
                ],
                'after_element_html' => __('Indicates whether or not autoplay will be enabled.'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldsetAutoPlaySettings->addField(
            'autoplayDelay',
            'text',
            [
                'name' => 'autoplayDelay',
                'label' => __('Auto Play Delay'),
                'title' => __('Auto Play Delay'),
                'class' => 'validate-not-negative-number',
                'after_element_html' => __('Sets the delay/interval (in milliseconds) at which the autoplay
                                            will run.'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldsetAutoPlaySettings->addField(
            'autoplayDirection',
            'select',
            [
                'name' => 'autoplayDirection',
                'label' => __('Auto Play Direction'),
                'title' => __('Auto Play Direction'),
                'values' => [
                    'normal' => __('Normal'),
                    'backwards' => __('Backwards'),
                ],
                'after_element_html' => __('Indicates whether autoplay will navigate to the next
                                            slide or previous slide.'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldsetAutoPlaySettings->addField(
            'autoplayOnHover',
            'select',
            [
                'name' => 'autoplayOnHover',
                'label' => __('Auto Play On Hover'),
                'title' => __('Auto Play On Hover'),
                'values' => [
                    'pause' => __('Pause'),
                    'stop' => __('Stop'),
                    'none' => __('None'),
                ],
                'after_element_html' => __('Indicates if the autoplay will be paused or stopped
                                            when the slider is hovered'),
                'disabled' => $isElementDisabled
            ]
        );

        $controlsSettings = $form->addFieldset('autoplay_controls_fieldset',
            ['legend' => __('Controls settings')]);

        $controlsSettings->addField(
            'arrows',
            'select',
            [
                'name' => 'arrows',
                'label' => __('Arrows'),
                'title' => __('Arrows'),
                'values' => [
                    'false' =>__('Disable'),
                    'true' =>__('Enable'),
                ],
                'after_element_html' => __('Indicates whether the arrow buttons will be created.'),
                'disabled' => $isElementDisabled
            ]
        );

        $controlsSettings->addField(
            'fadeArrows',
            'select',
            [
                'name' => 'fadeArrows',
                'label' => __('Fade Arrows'),
                'title' => __('Fade Arrows'),
                'values' => [
                    'true' =>__('Enable'),
                    'false' =>__('Disable'),
                ],
                'after_element_html' => __('Indicates whether the arrows will fade in only on hover.'),
                'disabled' => $isElementDisabled
            ]
        );

        $controlsSettings->addField(
            'buttons',
            'select',
            [
                'name' => 'buttons',
                'label' => __('Buttons'),
                'title' => __('Buttons'),
                'values' => [
                    'true' =>__('Enable'),
                    'false' =>__('Disable'),
                ],
                'after_element_html' => __('Indicates whether the buttons will be created.'),
                'disabled' => $isElementDisabled
            ]
        );

        $controlsSettings->addField(
            'keyboard',
            'select',
            [
                'name' => 'keyboard',
                'label' => __('Keyboard'),
                'title' => __('Keyboard'),
                'values' => [
                    'true' =>__('Enable'),
                    'false' =>__('Disable'),
                ],
                'after_element_html' => __('Indicates whether keyboard navigation
                                            will be enabled.'),
                'disabled' => $isElementDisabled
            ]
        );

        $controlsSettings->addField(
            'fullScreen',
            'select',
            [
                'name' => 'fullScreen',
                'label' => __('Full Screen'),
                'title' => __('Full Screen'),
                'values' => [
                    'false' =>__('Disable'),
                    'true' =>__('Enable'),
                ],
                'after_element_html' => __('Indicates whether the full-screen button is enabled.'),
                'disabled' => $isElementDisabled
            ]
        );

        $controlsSettings->addField(
            'fadeFullScreen',
            'select',
            [
                'name' => 'fadeFullScreen',
                'label' => __('Fade Full Screen'),
                'title' => __('Fade Full Screen'),
                'values' => [
                    'true' =>__('Enable'),
                    'false' =>__('Disable'),
                ],
                'after_element_html' => __('Indicates whether the button will fade in only on hover.'),
                'disabled' => $isElementDisabled
            ]
        );

        $responsiveSettings = $form->addFieldset('responsive_fieldset',
            ['legend' => __('Responsive settings')]);


        $responsiveSettings->addField(
            'responsive',
            'select',
            [
                'name' => 'responsive',
                'label' => __('Responsive'),
                'title' => __('Responsive'),
                'values' => [
                    'true' =>__('Enable'),
                    'false' =>__('Disable')
                ],
                'after_element_html' => __('Makes the slider responsive.
                                            The slider can be responsive even if the \'width\' and/or \'height\'
                                            properties are set to fixed values. In this situation, \'width\' and \'height\'
                                            will act as the maximum width and height of the slides.'),
                'disabled' => $isElementDisabled
            ]
        );

        $responsiveSettings->addField(
            'touchSwipe',
            'select',
            [
                'name' => 'touchSwipe',
                'label' => __('Touch Swipe'),
                'title' => __('Touch Swipe'),
                'values' => [
                    'true' =>__('Enable'),
                    'false' =>__('Disable'),
                ],
                'after_element_html' => __('Indicates whether the touch swipe will be enabled for slides.'),
                'disabled' => $isElementDisabled
            ]
        );

        $responsiveSettings->addField(
            'touchSwipeThreshold',
            'text',
            [
                'name' => 'touchSwipeThreshold',
                'label' => __('Touch Swipe Threshold'),
                'title' => __('Touch Swipe Threshold'),
                'class' => 'validate-not-negative-number',
                'after_element_html' => __('Sets the minimum amount that the slides should move.'),
                'disabled' => $isElementDisabled
            ]
        );

        $responsiveSettings->addField(
            'smallSize',
            'text',
            [
                'name' => 'smallSize',
                'label' => __('Small Size'),
                'title' => __('Small Size'),
                'class' => 'validate-not-negative-number',
                'after_element_html' => __('If the slider size is below this size,
                                            the small version of the images will be used.'),
                'disabled' => $isElementDisabled
            ]
        );

        $responsiveSettings->addField(
            'mediumSize',
            'text',
            [
                'name' => 'mediumSize',
                'label' => __('Medium Size'),
                'title' => __('Medium Size'),
                'class' => 'validate-not-negative-number',
                'after_element_html' => __('If the slider size is below this size, the medium
                                            version of the images will be used.'),
                'disabled' => $isElementDisabled
            ]
        );

        $responsiveSettings->addField(
            'largeSize',
            'text',
            [
                'name' => 'largeSize',
                'label' => __('Large Size'),
                'title' => __('Large Size'),
                'class' => 'validate-not-negative-number',
                'after_element_html' => __('If the slider size is below this size,
                                            the large version of the images will be used.'),
                'disabled' => $isElementDisabled
            ]
        );

        $captionSettings = $form->addFieldset('caption_fieldset',
            ['legend' => __('Caption settings')]);

        $captionSettings->addField(
            'fadeCaption',
            'select',
            [
                'name' => 'fadeCaption',
                'label' => __('Fade Caption'),
                'title' => __('Fade Caption'),
                'values' => [
                    'true' =>__('Enable'),
                    'false' =>__('Disable'),
                ],
                'after_element_html' => __('Indicates whether or not the captions will be faded.'),
                'disabled' => $isElementDisabled
            ]
        );

        $captionSettings->addField(
            'captionFadeDuration',
            'text',
            [
                'name' => 'captionFadeDuration',
                'label' => __('Caption Fade Duration'),
                'title' => __('Caption Fade Duration'),
                'class' => 'validate-not-negative-number',
                'after_element_html' => __('Sets the duration of the fade animation.'),
                'disabled' => $isElementDisabled
            ]
        );


        $layersMainSettings = $form->addFieldset('layers_main_fieldset',
            ['legend' => __('Layers main settings')]);

        $layersMainSettings->addField(
            'waitForLayers',
            'select',
            [
                'name' => 'waitForLayers',
                'label' => __('Wait For Layers'),
                'title' => __('Wait For Layers'),
                'values' => [
                    'false' =>__('Disable'),
                    'true' =>__('Enable'),
                ],
                'after_element_html' => __('Indicates whether the slider will wait for the layers
                                            to disappear before going to a new slide.'),
                'disabled' => $isElementDisabled
            ]
        );

        $layersMainSettings->addField(
            'autoScaleLayers',
            'select',
            [
                'name' => 'autoScaleLayers',
                'label' => __('Auto Scale Layers'),
                'title' => __('Auto Scale Layers'),
                'values' => [
                    'true' =>__('Enable'),
                    'false' =>__('Disable'),
                ],
                'after_element_html' => __('Indicates whether the layers will be scaled automatically.'),
                'disabled' => $isElementDisabled
            ]
        );

        $layersMainSettings->addField(
            'autoScaleReference',
            'text',
            [
                'name' => 'autoScaleReference',
                'label' => __('Auto Scale Reference'),
                'title' => __('Auto Scale Reference'),
                'class' => 'auto-scale-reference',
                'after_element_html' => __('Sets a reference width which will be compared to the current slider width
                                            in order to determine how much the layers need to scale down. '),
                'disabled' => $isElementDisabled
            ]
        );

        if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
        }
        $this->_eventManager->dispatch('adminhtml_film_slider_edit_tab_main_prepare_form', ['form' => $form]);


        if ($model->getId()) {
            $values =  $model->getData();
        } else {
            $values = [
                'store_id' => [0],
                'width' => '100%',
                'height' => '500',
                'responsive' => 'true',
                'aspectRatio' => '-1',
                'imageScaleMode'=>'cover',
                'centerImage' => 'true',
                'allowScaleUp' => 'true',
                'autoHeight' => 'false',
                'startSlide' => 0,
                'shuffle' => 'false',
                'orientation' => 'horizontal',
                'forceSize' => 'none',
                'loop' => 'true',
                'slideDistance' => 10,
                'slideAnimationDuration' => 700,
                'heightAnimationDuration' => 700,
                'visibleSize' => 'auto',
                'fade' => 'false',
                'fadeOutPreviousSlide' => 'true',
                'fadeDuration' => 500,
                'autoplay' => 'true',
                'autoplayDelay' => 5000,
                'autoplayDirection' => 'normal',
                'autoplayOnHover' => 'pause',
                'arrows' => 'false',
                'fadeArrows' => 'true',
                'buttons' => 'true',
                'keyboard' => 'true',
                'keyboardOnlyOnFocus' => 'false',
                'touchSwipe'  => 'true',
                'touchSwipeThreshold' => 50,
                'fadeCaption'  => 'true',
                'captionFadeDuration' => 5000,
                'fullScreen' => 'false',
                'fadeFullScreen'  => 'true',
                'waitForLayers' => 'false',
                'autoScaleLayers'  => 'true',
                'autoScaleReference' => '-1',
                'smallSize' => 480,
                'mediumSize' => 768,
                'largeSize' => 1024,
                'updateHash' => 'false',
                'reachVideoAction' => 'none',
                'leaveVideoAction' => 'pauseVideo',
                'playVideoAction' => 'stopAutoplay',
                'pauseVideoAction' => 'none',
                'endVideoAction' => 'none',
                'thumbnailWidth' => 100,
                'thumbnailHeight' => 70,
                'thumbnailsPosition' => 'bottom',
                'thumbnailPointer' => 'false',
                'thumbnailArrows' => 'false',
                'fadeThumbnailArrows' => 'true',
                'thumbnailTouchSwipe' => 'true',
            ];
        }

        $form->setValues($values);
        $this->setForm($form);


        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('General Options');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('General Options');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
