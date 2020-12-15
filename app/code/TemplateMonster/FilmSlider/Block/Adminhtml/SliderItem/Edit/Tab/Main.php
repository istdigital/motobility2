<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
namespace TemplateMonster\FilmSlider\Block\Adminhtml\SliderItem\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Main extends Generic implements TabInterface {
	/**
	 * @var \Magento\Store\Model\System\Store
	 */
	protected $_systemStore;

	/**
	 * @var \Magento\Cms\Model\Wysiwyg\Config
	 */
	protected $_wysiwygConfig;

	protected $_layerKlass;

	protected $_position;

	protected $_showTransition;

	protected $_hideTransition;

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
		\Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
		\TemplateMonster\FilmSlider\Model\Animation\Source\LayerKlass $layerKlass,
		\TemplateMonster\FilmSlider\Model\Animation\Source\Position $position,
		\TemplateMonster\FilmSlider\Model\Animation\Source\ShowTransition $showTransition,
		\TemplateMonster\FilmSlider\Model\Animation\Source\HideTransition $hideTransition,
		array $data = []
	) {
		$this->_systemStore = $systemStore;
		$this->_wysiwygConfig = $wysiwygConfig;
		$this->_layerKlass = $layerKlass;
		$this->_position = $position;
		$this->_showTransition = $showTransition;
		$this->_hideTransition = $hideTransition;
		parent::__construct($context, $registry, $formFactory, $data);
	}

	protected function _prepareLayout() {
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
	protected function _prepareForm() {
		$model = $this->_coreRegistry->registry(\TemplateMonster\FilmSlider\Model\SliderItem::REGISTRY_NAME);

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

		$fieldsetGeneral = $form->addFieldset('base_fieldset', ['legend' => __('Slide Information')]);
		if ($model->getId()) {
			$fieldsetGeneral->addField('slideritem_id', 'hidden', ['name' => 'slideritem_id']);
		}

		$fieldsetGeneral->addField('parent_id', 'hidden', ['name' => 'parent_id']);

		$fieldsetGeneral->addField(
			'title',
			'text',
			[
				'name' => 'title',
				'label' => __('Slide title'),
				'title' => __('Slide title'),
				'required' => true,
				'disabled' => $isElementDisabled,
			]
		);

		$fieldsetGeneral->addField(
			'status',
			'select',
			[
				'label' => __('Status'),
				'title' => __('Status'),
				'name' => 'status',
				'required' => true,
				'values' => [
					true => __('Enable'),
					false => __('Disable'),
				],
				'disabled' => $isElementDisabled,
			]
		);

		$fieldsetGeneral->addField(
			'sort_item',
			'text',
			[
				'name' => 'sort_item',
				'label' => __('Sort item'),
				'title' => __('Sort item'),
				'required' => false,
				'disabled' => $isElementDisabled,
			]
		);

		$fieldsetSliderImage = $form->addFieldset('base_fieldset_image',
			['legend' => __('Slide Images')]
		);

		$fieldsetSliderImage->addField(
			'link',
			'text',
			[
				'name' => 'link',
				'label' => __('Link'),
				'title' => __('Link'),
				'disabled' => $isElementDisabled,
			]
		);

		$fieldsetSliderImage->addField(
			'mobile_image',
			'text',
			[
				'name' => 'mobile_image',
				'label' => __('Mobile Image'),
				'title' => __('Mobile Image'),
				'disabled' => $isElementDisabled,
			]
		);

		$fieldsetSliderImage->addField(
			'video',
			'text',
			[
				'name' => 'video',
				'label' => __('Video'),
				'title' => __('Video'),
				'disabled' => $isElementDisabled,
			]
		);

		$fieldsetSliderImage->addField(
			'image',
			'text',
			[
				'name' => 'image',
				'label' => __('Image'),
				'title' => __('Image'),
				'disabled' => $isElementDisabled,
			]
		);

		$fieldsetSliderImage->addType('imagecanvas',
			'TemplateMonster\FilmSlider\Block\Adminhtml\SliderItem\Widget\Element\Image\Canvas');
		$fieldsetSliderImage->addField(
			'image_canvas',
			'imagecanvas',
			[
				'name' => 'image_canvas',
				'label' => __('Params'),
				'title' => __('Params'),
				'disabled' => $isElementDisabled,
			]
		);

		$fieldsetSliderImage->addField(
			'preview_width',
			'text',
			[
				'name' => 'preview_width',
				'label' => __('Preview Width'),
				'title' => __('Preview Width'),
				'disabled' => $isElementDisabled,
			]
		);

		$fieldsetSliderImage->addField(
			'preview_height',
			'text',
			[
				'name' => 'preview_height',
				'label' => __('Preview Height'),
				'title' => __('Preview Height'),
				'disabled' => $isElementDisabled,
			]
		);

		$fieldsetSliderImage->addType('layer_sortable_feldset',
			'TemplateMonster\FilmSlider\Block\Adminhtml\SliderItem\Widget\Element\Layer\Sortable');

		$fieldsetLayerSort = $form->addFieldset('base_fieldset_layer_sortable',
			[
				'legend' => __('Layers list'),
			]
		);

		$fieldsetLayerSort->addType('imageinsert',
			'TemplateMonster\FilmSlider\Block\Adminhtml\SliderItem\Widget\Element\Image\Insert');
		$fieldsetLayerSort->addField(
			'image_insert',
			'imageinsert',
			[
				'name' => 'image_insert',
				'label' => __('Insert Image'),
				'title' => __('Insert Image'),
				'disabled' => $isElementDisabled,
			]
		);

		$fieldsetLayerSort->addField(
			'layersortable',
			'text',
			[
				'name' => 'Layout Sortable',
			]
		);

		$fieldsetLayerText = $form->addFieldset('base_fieldset_layer_text',
			[
				'legend' => __('Layer text settings'),
			]
		);

		$fieldsetLayerText->addField(
			'layer_text_html',
			'textarea',
			[
				'name' => 'layer_text_html',
				'label' => __('Layer text/html'),
				'title' => __('Layer text/html'),
				'disabled' => true,
			]
		);

		$fieldsetLayerText->addField(
			'layer_text_width',
			'text',
			[
				'name' => 'layer_text_width',
				'label' => __('Layer Width'),
				'title' => __('Layer Width'),
				'class' => 'validate-zero-or-greater',
				'after_element_html' => __('Sets the width of the layer.'),
				'disabled' => true,
			]
		);

		$fieldsetLayerText->addField(
			'layer_text_height',
			'text',
			[
				'name' => 'layer_text_height',
				'label' => __('Layer Height'),
				'title' => __('Layer Height'),
				'class' => 'validate-zero-or-greater',
				'after_element_html' => __('Sets the height of the layer.'),
				'disabled' => true,
			]
		);

		$fieldsetLayerText->addField(
			'layer_text_back_color',
			'text',
			[
				'name' => 'layer_text_back_color',
				'label' => __('Background color'),
				'title' => __('Background color'),
				'class' => 'field-color-picker',
				'disabled' => true,
			]
		);

		$fieldsetLayerText->addField(
			'layer_text_back_opacity',
			'text',
			[
				'name' => 'layer_text_back_opacity',
				'label' => __('Background opacity'),
				'title' => __('Background opacity'),
				'class' => 'validate-not-negative-number validate-number-range number-range-0-1',
				'disabled' => true,
			]
		);

		$fieldsetLayerText->addField(
			'layer_text_color',
			'text',
			[
				'name' => 'layer_text_color',
				'label' => __('Text color'),
				'title' => __('Text color'),
				'class' => 'field-color-picker',
				'disabled' => true,
			]
		);

		$fieldsetLayerText->addField(
			'layer_text_font_size',
			'text',
			[
				'name' => 'layer_text_font_size',
				'label' => __('Font size'),
				'title' => __('Font size'),
				'class' => 'validate-zero-or-greater',
				'disabled' => true,
			]
		);

		$fieldsetLayerText->addField(
			'layer_text_line_height',
			'text',
			[
				'name' => 'layer_text_line_height',
				'label' => __('Line height'),
				'title' => __('Line height'),
				'class' => 'validate-zero-or-greater',
				'disabled' => true,
			]
		);

		$fieldsetLayerText->addField(
			'layer_text_font_style',
			'select',
			[
				'name' => 'layer_text_font_style',
				'label' => __('Font style'),
				'title' => __('Font style'),
				'disabled' => true,
				'values' => [
					'normal' => __('Normal'),
					'italic' => __('Italic'),
					'oblique' => __('Oblique'),
				],
			]
		);

		$fieldsetLayerText->addField(
			'layer_text_font_weight',
			'select',
			[
				'name' => 'layer_text_font_weight',
				'label' => __('Font weight'),
				'title' => __('Font weight'),
				'disabled' => true,
				'values' => [
					'100' => __('100'),
					'200' => __('200'),
					'300' => __('300'),
					'400' => __('400'),
					'500' => __('500'),
					'600' => __('600'),
					'700' => __('700'),
					'800' => __('800'),
					'900' => __('900'),
				],
			]
		);

		$fieldsetLayerText->addField(
			'layer_text_font_family',
			'text',
			[
				'name' => 'layer_text_font_family',
				'label' => __('Font family'),
				'title' => __('Font family'),
				'disabled' => true,
			]
		);

		$fieldsetLayerText->addField(
			'layer_text_indent',
			'text',
			[
				'name' => 'layer_text_indent',
				'label' => __('Text indent'),
				'title' => __('Text indent'),
				'class' => 'validate-zero-or-greater',
				'disabled' => true,
			]
		);

		$fieldsetLayer = $form->addFieldset('base_fieldset_layer',
			[
				'legend' => __('Layer position settings'),
			]
		);

		$fieldsetLayer->addField(
			'layer_images',
			'hidden',
			[
				'name' => 'layer_images',
				'label' => __('Layer Image'),
				'title' => __('Layer Image'),
				'disabled' => $isElementDisabled,
			]
		);

		$fieldsetLayer->addField(
			'layer_images_update',
			'hidden',
			[
				'id' => 'slider_layer_images_update',
				'name' => 'layer_images_update',
				'label' => __('Layer Image Update'),
				'title' => __('Layer Image Update'),
				'disabled' => $isElementDisabled,
			]
		);

		$fieldsetLayer->addField(
			'preview_name_layer',
			'text',
			[
				'name' => 'preview_name_layer',
				'label' => __('Preview Name'),
				'title' => __('Preview Name'),
				'disabled' => $isElementDisabled,
			]
		);

		$fieldsetLayer->addField(
			'preview_width_layer',
			'text',
			[
				'name' => 'data-width',
				'label' => __('Preview Width'),
				'title' => __('Preview Width'),
				'class' => 'validate-not-negative-number',
				'after_element_html' => __('Sets the width of the layer'),
				'disabled' => $isElementDisabled,
			]
		);

		$fieldsetLayer->addField(
			'preview_height_layer',
			'text',
			[
				'name' => 'data-height',
				'label' => __('Preview Height'),
				'title' => __('Preview Height'),
				'class' => 'validate-not-negative-number',
				'after_element_html' => __('Sets the height of the layer'),
				'disabled' => $isElementDisabled,
			]
		);

		$fieldsetLayer->addField(
			'depth',
			'text',
			[
				'name' => 'data-depth',
				'label' => __('Depth'),
				'title' => __('Depth'),
				'class' => 'validate-not-negative-number',
				'after_element_html' => __('Sets the depth (z-index, in CSS terms) of the layer.'),
				'disabled' => $isElementDisabled,
			]
		);

		$fieldsetLayer->addField(
			'preview_horizontal_layer',
			'text',
			[
				'name' => 'data-horizontal',
				'label' => __('Horizontal Position'),
				'title' => __('Horizontal Position'),
				'class' => 'validate-int-or-percentage',
				'after_element_html' => __('Sets the horizontal position of the layer, using the value specified for
                                            data-position as a reference point. '),
				'disabled' => $isElementDisabled,
			]
		);

		$fieldsetLayer->addField(
			'preview_vertical_layer',
			'text',
			[
				'name' => 'data-vertical',
				'label' => __('Vertical Position'),
				'title' => __('Vertical Position'),
				'class' => 'validate-int-or-percentage',
				'after_element_html' => __('Sets the vertical position of the layer, using the value specified for
                                            data-position as a reference point. '),
				'disabled' => $isElementDisabled,
			]
		);

		$fieldsetLayer->addField(
			'css_class',
			'text',
			[
				'name' => 'css-class',
				'label' => __('CSS Class'),
				'title' => __('CSS Class'),
				// 'class' => 'validate-int-or-percentage',
				'after_element_html' => __('Sets the css class for layer.'),
				'disabled' => $isElementDisabled,
			]
		);

		$fieldsetLayer->addField(
			'sort_order',
			'hidden',
			[
				'name' => 'sortOrder',
				'label' => __('sortOrder'),
				'title' => __('sortOrder'),
				'disabled' => $isElementDisabled,
			]
		);

		$fieldsetLayerAnimation = $form->addFieldset('base_fieldset_layer_animation',
			[
				'legend' => __('Layer animation settings'),
			]
		);

		$fieldsetLayerAnimation->addField(
			'position',
			'select',
			[
				'id' => 'position',
				'name' => 'data-position',
				'label' => __('Layer Position'),
				'title' => __('Layer Position'),
				'values' => $this->_position->getValues(),
				'after_element_html' => __('Sets the position of the layer.'),
				'disabled' => $isElementDisabled,
			]
		);

		$fieldsetLayerAnimation->addField(
			'show_transition',
			'select',
			[
				'id' => 'data-show-transition',
				'name' => 'data-show-transition',
				'label' => __('Show Transition'),
				'title' => __('Show Transition'),
				'values' => $this->_showTransition->getValues(),
				'after_element_html' => __('Sets the transition of the layer when it appears in the slide.'),
				'disabled' => $isElementDisabled,
			]
		);

		$fieldsetLayerAnimation->addField(
			'show_offset',
			'text',
			[
				'name' => 'data-show-offset',
				'label' => __('Show Offset'),
				'title' => __('Show Offset'),
				'class' => 'validate-not-negative-number',
				'after_element_html' => __('Sets an offset for the position of the layer towards which the layer will
                                            be animated from the original position when it disappears
                                            from the slide. Needs to be set to a fixed value.'),
				'disabled' => $isElementDisabled,
			]
		);

		$fieldsetLayerAnimation->addField(
			'show_duration',
			'text',
			[
				'name' => 'data-show-duration',
				'label' => __('Show Duration'),
				'title' => __('Show Duration'),
				'class' => 'validate-zero-or-greater',
				'after_element_html' => __('Sets the duration of the show transition.'),
				'disabled' => $isElementDisabled,
			]
		);

		$fieldsetLayerAnimation->addField(
			'show_delay',
			'text',
			[
				'name' => 'data-show-delay',
				'label' => __('Show Delay'),
				'title' => __('Show Delay'),
				'class' => 'validate-zero-or-greater',
				'after_element_html' => __('Sets a delay for the show transition.'),
				'disabled' => $isElementDisabled,
			]
		);

		$fieldsetLayerAnimation->addField(
			'hide_transition',
			'select',
			[
				'name' => 'data-hide-transition',
				'label' => __('Hide Transition'),
				'title' => __('Hide Transition'),
				'after_element_html' => __('Sets the transition of the layer when it disappears from the slide.'),
				'values' => $this->_hideTransition->getValues(),
				'disabled' => $isElementDisabled,
			]
		);

		$fieldsetLayerAnimation->addField(
			'hide_duration',
			'text',
			[
				'name' => 'data-hide-duration',
				'label' => __('Hide Duration'),
				'title' => __('Hide Duration'),
				'class' => 'validate-zero-or-greater',
				'after_element_html' => __('Sets the duration of the hide transition.'),
				'disabled' => $isElementDisabled,
			]
		);

		$fieldsetLayerAnimation->addField(
			'hide_offset',
			'text',
			[
				'name' => 'data-hide-offset',
				'label' => __('Hide Offset'),
				'title' => __('Hide Offset'),
				'class' => 'validate-not-negative-number',
				'after_element_html' => __('Sets an offset for the position of the layer towards which the layer will
                                            be animated from the original position when it disappears
                                            from the slide. Needs to be set to a fixed value.'),
				'disabled' => $isElementDisabled,
			]
		);

		$fieldsetLayerAnimation->addField(
			'hide_delay',
			'text',
			[
				'name' => 'data-hide-delay',
				'label' => __('Hide Delay'),
				'title' => __('Hide Delay'),
				'class' => 'validate-zero-or-greater',
				'after_element_html' => __('Sets a delay for the hide transition.'),
				'disabled' => $isElementDisabled,
			]
		);

		$fieldsetLayerAnimation->addField(
			'stay_duration',
			'text',
			[
				'name' => 'data-stay-duration',
				'label' => __('Stay Duration'),
				'title' => __('Stay Duration'),
				'class' => 'validate-zero-or-greater',
				'after_element_html' => __('Sets how much time a layer will stay visible
                                            before being hidden automatically.'),
				'disabled' => $isElementDisabled,
			]
		);

		$fieldsetLayerAnimation->addField(
			'image_params',
			'hidden',
			[
				'name' => 'image_params',
				'label' => __('Image Params'),
				'title' => __('Image Params'),
				'disabled' => $isElementDisabled,
			]
		);

		$fieldsetLayerAnimation->addField(
			'layer_general_params',
			'hidden',
			[
				'id' => 'layer_general_params',
				'name' => 'layer_general_params',
				'label' => __('Json Items'),
				'title' => __('Json Items'),
				'disabled' => $isElementDisabled,
			]
		);

		$imageinsert = $this->getLayout()->createBlock(
			'TemplateMonster\FilmSlider\Block\Adminhtml\SliderItem\Widget\Render\SliderItem\ImageInsert',
			'slideritem.imageinsert.render');

		$image = $this->getLayout()->createBlock(
			'TemplateMonster\FilmSlider\Block\Adminhtml\SliderItem\Widget\Render\SliderItem\Image',
			'slideritem.image.render');

		$mobileimage = $this->getLayout()->createBlock(
			'TemplateMonster\FilmSlider\Block\Adminhtml\SliderItem\Widget\Render\SliderItem\MobileImage',
			'slideritem.mobileimage.render');

		$video = $this->getLayout()->createBlock(
			'TemplateMonster\FilmSlider\Block\Adminhtml\SliderItem\Widget\Render\SliderItem\Video',
			'slideritem.video.render');

		$imageCanvas = $this->getLayout()->createBlock(
			'TemplateMonster\FilmSlider\Block\Adminhtml\SliderItem\Widget\Render\SliderItem\Canvas\Image',
			'slideritem.canvas.image.render');

		$imageSortable = $this->getLayout()->createBlock(
			'TemplateMonster\FilmSlider\Block\Adminhtml\SliderItem\Widget\Render\SliderItem\Layer\Sortable',
			'slideritem.layer.sortable.render');

		$form->getElement('image_insert')->setRenderer($imageinsert);
		$form->getElement('image')->setRenderer($image);
		$form->getElement('mobile_image')->setRenderer($mobileimage);
		$form->getElement('video')->setRenderer($video);
		$form->getElement('image_canvas')->setRenderer($imageCanvas);
		$form->getElement('layersortable')->setRenderer($imageSortable);

		if (!$model->getId()) {
			$model->setData('status', $isElementDisabled ? '0' : '1');
		}

		$this->_eventManager->dispatch('adminhtml_film_slider_edit_tab_main_prepare_form', ['form' => $form]);

		if ($model->getId()) {
			$values = $model->getData();
		} else {
			$values = [
				'show_transition' => 'left',
				'hide_transition' => 'left',
				'parent_id' => $model->getParentId(),
				'slider_item_id' => $model->getId(),
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
	public function getTabLabel() {
		return __('Slider Item Options');
	}

	/**
	 * Prepare title for tab
	 *
	 * @return \Magento\Framework\Phrase
	 */
	public function getTabTitle() {
		return __('Slider Item Options');
	}

	/**
	 * {@inheritdoc}
	 */
	public function canShowTab() {
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isHidden() {
		return false;
	}

	/**
	 * Check permission for passed action
	 *
	 * @param string $resourceId
	 * @return bool
	 */
	protected function _isAllowedAction($resourceId) {
		return $this->_authorization->isAllowed($resourceId);
	}
}
