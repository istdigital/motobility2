<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FilmSlider\Block\Adminhtml\SliderItem\Widget\Render\SliderItem;

use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Backend\Block\Widget;
use Magento\Backend\Block\Widget\Button;
use Magento\Backend\Block\Template\Context;
use TemplateMonster\FilmSlider\Block\Adminhtml\SliderItem\Widget\Render\SliderItem\Canvas\Image as ImageRender;

class Image extends Widget implements RendererInterface
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
        $this->setTemplate('TemplateMonster_FilmSlider::slideritem/widget/render/slideritem/image.phtml');
        parent::_construct();
    }

    protected function _beforeToHtml()
    {
        $buttonImage = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button',
            'slideritem.image.button'
        )->setData(
            [
                'label' => __('Insert Image'),
                'type' => 'button',
                'title' => __('Insert Image'),
                'onclick' => "MediabrowserUtility.openDialog('" .
                        $this->_backendUrl->getUrl('cms/wysiwyg_images/index',
                            [
                                'target_element_id' => $this->getElement()->getHtmlId(),
                                ImageRender::ELEMENT_NAME => true
                            ]
                        )
                . "',null,null,null,{'closed':true})",
                'class' => 'action-add-image plugin'
            ]
        );

        $this->setChild('slideritem.image.button', $buttonImage);

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
