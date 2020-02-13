<?php

namespace TemplateMonster\Parallax\Block\Adminhtml\BlockItem\Edit;

use TemplateMonster\Parallax\Helper\Data as ParallaxHelper;
use TemplateMonster\Parallax\Api\Data\BlockItemInterface;
use TemplateMonster\Parallax\Model\Config\Source\Type;
use TemplateMonster\Parallax\Model\Config\Source\VideoFormat;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory;
use Magento\Config\Model\Config\Source\Enabledisable;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

/**
 * Block item form.
 *
 * @package TemplateMonster\Parallax\Block\Adminhtml\BlockItem\Edit
 */
class Form extends Generic
{
    /**
     * @var Type
     */
    protected $_type;

    /**
     * @var VideoFormat
     */
    protected $_videoFormat;

    /**
     * @var Enabledisable
     */
    protected $_enabledisable;

    /**
     * @var FieldFactory
     */
    protected $_fieldFactory;

    /**
     * @var Config
     */
    protected $_wysiwygConfig;

    /**
     * Form constructor.
     *
     * @param Type          $type
     * @param VideoFormat   $videoFormat
     * @param Enabledisable $enabledisable
     * @param FieldFactory  $fieldFactory
     * @param Context       $context
     * @param Registry      $registry
     * @param FormFactory   $formFactory
     * @param Config        $wysiwygConfig
     * @param array         $data
     */
    public function __construct(
        Type $type,
        VideoFormat $videoFormat,
        Enabledisable $enabledisable,
        FieldFactory $fieldFactory,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Config $wysiwygConfig,
        array $data = []
    ) {
        $this->_type = $type;
        $this->_videoFormat = $videoFormat;
        $this->_enabledisable = $enabledisable;
        $this->_fieldFactory = $fieldFactory;
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        $form = $this->_buildForm();
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return \Magento\Framework\Data\Form
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _buildForm()
    {
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'method' => 'post',
                    'action' => $this->getData('action'),
                    'enctype' => 'multipart/form-data',
                ],
            ]
        );
        $form->setUseContainer(true);
        $form->setHtmlIdPrefix('parallax_item_');

        $fieldset = $form->addFieldset('general', [
            'legend' => __('General Settings'),
        ]);

        $model = $this->_getModel();

        if ($model->getId()) {
            $fieldset->addField('item_id', 'hidden', [
                'name' => 'item_id'
            ]);
        }

        $fieldset->addField('block_id', 'hidden', [
            'name' => 'block_id'
        ]);

        $fieldset->addField('name', 'text', [
            'name' => 'name',
            'label' => __('Item name'),
            'title' => __('Item name'),
            'required' => true,
        ]);

        $fieldset->addField('status', 'select', [
            'name' => 'status',
            'label' => __('Status'),
            'title' => __('Status'),
            'required' => true,
            'values' => $this->_enabledisable->toOptionArray(),
            'note' => 'Status of the block item.'
        ]);

        $fieldset->addField('type', 'select', [
            'name' => 'type',
            'label' => __('Type'),
            'title' => __('Type'),
            'required' => true,
            'values' => $this->_type->toOptionArray()
        ]);

        $fieldset->addField('image', 'TemplateMonster\Parallax\Block\Adminhtml\Form\Element\Image', [
            'name' => 'image',
            'label' => __('Image'),
            'title' => __('Image'),
            'required' => true,
            'note' => __('Image file to upload, %1mb max size.', ParallaxHelper::IMAGE_MAX_SIZE)
        ]);

        $fieldset->addField('video_format', 'select', [
            'name' => 'video_format',
            'label' => __('Video Format'),
            'title' => __('Video Format'),
            'required' => true,
            'values' => $this->_videoFormat->toOptionArray(),
            'note' => __('Choose from the available video formats.'),
        ]);

        $fieldset->addField('video_id', 'text', [
            'name' => 'video_id',
            'label' => __('Video Id'),
            'title' => __('Video Id'),
            'required' => true,
            'note' => __('YouTube video identifier.'),
        ]);

        $fieldset->addField('video_mp4', 'TemplateMonster\Parallax\Block\Adminhtml\Form\Element\Video', [
            'name' => 'video_mp4',
            'label' => __('Video Mp4'),
            'title' => __('Video Mp4'),
            'note' => __('Video file in MP4-format, %1mb max size.', ParallaxHelper::VIDEO_MAX_SIZE),
            'required' => false,
        ]);

        $fieldset->addField('video_webm', 'TemplateMonster\Parallax\Block\Adminhtml\Form\Element\Video', [
            'name' => 'video_webm',
            'label' => __('Video WebM'),
            'title' => __('Video WebM'),
            'note' => __('Video file in WebM-format, %1mb max size.', ParallaxHelper::VIDEO_MAX_SIZE),
            'required' => false,
        ]);

        $fieldset->addField('text', 'editor', [
            'name' => 'text',
            'config' => $this->_wysiwygConfig->getConfig([
                'hidden' => true,
            ]),
            'note' => __('Text content of the block item.'),
        ]);

        $fieldset->addField('offset', 'text', [
            'name' => 'offset',
            'label' => __('Offset'),
            'title' => __('Offset'),
            'required' => true,
        ]);

        $fieldset->addField('is_inverse', 'select', [
            'name' => 'is_inverse',
            'label' => __('Inverse'),
            'title' => __('Inverse'),
            'required' => true,
            'values' => $this->_enabledisable->toOptionArray(),
            'note' => 'Determines the direction of parallax motion.',
        ]);

        $fieldset->addField('layout_speed', 'text', [
            'name' => 'layout_speed',
            'label' => __('Layout speed'),
            'title' => __('Layout speed'),
            'required' => true,
            'class' => 'validate-number validate-number-range number-range-0-2',
            'note' => 'Determines speed of the parallax block relative to scroll bar. Should be a value between 0 and 2, for example 0.5',
        ]);

        $fieldset->addField('sort_order', 'text', [
            'name' => 'sort_order',
            'label' => __('Sort Order'),
            'title' => __('Sort Order'),
            'required' => true,
            'class' => 'validate-number',
            'note' => 'Sort order of the block item.',
        ]);

        $fieldset->addField('is_fade', 'select', [
            'name' => 'is_fade',
            'label' => __('Fade'),
            'title' => __('Fade'),
            'required' => true,
            'values' => $this->_enabledisable->toOptionArray(),
            'note' => 'If set to true, layer will be gradually emerge from full transparency to full opacity depending on the position of the scroll layer.',
        ]);

        $fieldset->addField('css_class', 'text', [
            'name' => 'css_class',
            'label' => __('CSS-class'),
            'title' => __('CSS-class'),
            'note' => 'CSS-class of the block item.',
        ]);

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Form\Element\Dependence'
            )->addFieldMap(
                'parallax_item_type',
                'type'
            )->addFieldMap(
                'parallax_item_image',
                'image'
            )->addFieldMap(
                'parallax_item_video_format',
                'video_format'
            )->addFieldMap(
                'parallax_item_video_id',
                'video_id'
            )->addFieldMap(
                'parallax_item_video_mp4',
                'video_mp4'
            )->addFieldMap(
                'parallax_item_video_webm',
                'video_webm'
            )->addFieldMap(
                'parallax_item_text',
                'text'
            )->addFieldDependence(
                'image',
                'type',
                $this->_fieldFactory->create([
                    'fieldData' => [
                        'value' => implode(',', [
                            BlockItemInterface::TYPE_BACKGROUND_IMAGE,
                            BlockItemInterface::TYPE_BACKGROUND_VIDEO,
                            BlockItemInterface::TYPE_IMAGE,
                        ]),
                        'separator' => ',',
                    ],
                    'fieldPrefix' => ''
                ])
            )->addFieldDependence(
                'video_format',
                'type',
                BlockItemInterface::TYPE_BACKGROUND_VIDEO
            )->addFieldDependence(
                'video_id',
                'type',
                BlockItemInterface::TYPE_BACKGROUND_VIDEO
            )->addFieldDependence(
                'video_id',
                'video_format',
                BlockItemInterface::VIDEO_FORMAT_YOUTUBE
            )->addFieldDependence(
                'video_mp4',
                'type',
                BlockItemInterface::TYPE_BACKGROUND_VIDEO
            )->addFieldDependence(
                'video_webm',
                'type',
                BlockItemInterface::TYPE_BACKGROUND_VIDEO
            )->addFieldDependence(
                'video_webm',
                'video_format',
                BlockItemInterface::VIDEO_FORMAT_STATIC
            )->addFieldDependence(
                'video_mp4',
                'video_format',
                BlockItemInterface::VIDEO_FORMAT_STATIC
            )
            ->addFieldDependence(
                'text',
                'type',
                BlockItemInterface::TYPE_TEXT
            )
        );

        if ($model->getId()) {
            $form->setValues($model->getData());
        }
        else {
            $form->setValues([
                'sort_order' => '0',
                'offset' => '0',
                'status' => BlockItemInterface::STATUS_ENABLED,
                'block_id' => $this->getRequest()->getParam('block_id')
            ]);
        }

        return $form;
    }

    /**
     * @return BlockItemInterface
     */
    protected function _getModel()
    {
        return $this->_coreRegistry->registry('current_parallax_block_item');
    }
}
