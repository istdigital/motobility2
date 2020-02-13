<?php
/**
 * Created by MageRainbow. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Date: 11.03.16
 * Time: 12:19
 */

namespace TemplateMonster\FilmSlider\Block\Adminhtml\SliderItem\Widget\Render\SliderItem;

use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Backend\Block\Widget;
use Magento\Backend\Block\Widget\Button;
use Magento\Backend\Block\Template\Context;
use TemplateMonster\FilmSlider\Block\Adminhtml\SliderItem\Widget\Render\SliderItem\Canvas\Image as ImageRender;

class ImageInsert extends Widget implements RendererInterface
{
    protected $_element;

    protected $_button;

    protected $_backendUrl;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(Context $context, \Magento\Backend\Model\UrlInterface $backendUrl, array $data = [])
    {
        $this->_backendUrl = $backendUrl;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->setTemplate('TemplateMonster_FilmSlider::slideritem/widget/render/slideritem/insertimage.phtml');
        parent::_construct();
    }

    protected function _beforeToHtml()
    {
        $buttonLayer = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button',
            'slideritem.image.buttonlayer'
        )->setData(
            [
                'id' => 'add_layer_button',
                'label' => __('Add Layer Image'),
                'type' => 'button',
                'title' => __('Add Layer Image...'),
                'onclick' => "MediabrowserUtility.openDialog('" .
                    $this->_backendUrl->getUrl('cms/wysiwyg_images/index',
                        [
                            'target_element_id' => 'slider_layer_images',
                            ImageRender::ELEMENT_NAME => true
                        ]
                    )
                    . "',null,null,null,{'closed':true})",
                'class' => 'action-add-image plugin'
            ]
        );

        $buttonLayerTextAdd = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button',
            'slideritem.text.buttonlayer'
        )->setData(
            [
                'id' => 'add_layer_text_button',
                'label' => __('Add Layer Text'),
                'type' => 'button',
                'title' => __('Add Layer Text...'),
                'class' => 'action-add-text plugin'
            ]
        );

        $buttonLayerRemove = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button',
            'slideritem.image.buttonlayer.remove'
        )->setData(
            [
                'id' => 'remove_layer_button',
                'label' => __('Remove Layer'),
                'type' => 'button',
                'title' => __('Remove Layer'),
                'class' => 'action-remove-layer',
                'disabled' => true
            ]
        );

        $buttonLayerAllRemove = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button',
            'slideritem.image.buttonlayer.remove.all'
        )->setData(
            [
                'id' => 'remove_layer_all_button',
                'label' => __('Remove All Layer'),
                'type' => 'button',
                'title' => __('Remove All Layer'),
                'class' => 'action-remove-layer-all',
                'disabled' => true
            ]
        );

        $buttonLayerUpdate = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button',
            'slideritem.image.buttonlayer.update'
        )->setData(
            [
                'id' => 'update_layer_button',
                'label' => __('Update Layer'),
                'onclick' => "MediabrowserUtility.openDialog('" .
                    $this->_backendUrl->getUrl('cms/wysiwyg_images/index',
                        [
                            'target_element_id' => 'slider_layer_images_update',
                            ImageRender::ELEMENT_NAME => true
                        ]
                    )
                    . "',null,null,null,{'closed':true})",
                'type' => 'button',
                'title' => __('Update Layer'),
                'class' => 'action-update-layer-all',
                'disabled' => true
            ]
        );

        $buttonLayerDuplicate = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button',
            'slideritem.image.buttonlayer.duplicate'
        )->setData(
            [
                'id' => 'duplicate_layer_button',
                'label' => __('Duplicate Layer'),
                'type' => 'button',
                'title' => __('Duplicate Layer'),
                'class' => 'action-duplicate-layer-all',
                'disabled' => true
            ]
        );

        $this->setChild('slideritem.image.buttonlayer', $buttonLayer);
        $this->setChild('slideritem.text.buttonlayer', $buttonLayerTextAdd);
        $this->setChild('slideritem.image.buttonlayer.remove', $buttonLayerRemove);
        $this->setChild('slideritem.image.buttonlayer.remove.all', $buttonLayerAllRemove);
        $this->setChild('slideritem.image.buttonlayer.update', $buttonLayerUpdate);
        $this->setChild('slideritem.image.buttonlayer.duplicate', $buttonLayerDuplicate);

        return parent::_beforeToHtml();
    }

    public function getElement()
    {
        return $this->_element;
    }

    public function setElement(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_element = $element;
    }

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }
}
