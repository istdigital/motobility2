<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FilmSlider\Block\Adminhtml\SliderItem\Widget\Render\SliderItem\Canvas;

use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Backend\Block\Widget;
use Magento\Backend\Block\Widget\Button;
use Magento\Cms\Helper\Wysiwyg\Images;
use Magento\Backend\Block\Template\Context;

class Image extends Widget implements RendererInterface
{

    const ELEMENT_NAME = 'imagecanvas';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(Context $context, Images $image, array $data = [])
    {
        parent::__construct($context, $data);
    }


    protected function _construct()
    {
        $this->setTemplate('TemplateMonster_FilmSlider::slideritem/widget/render/slideritem/canvas/image.phtml');
        parent::_construct();
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();
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
