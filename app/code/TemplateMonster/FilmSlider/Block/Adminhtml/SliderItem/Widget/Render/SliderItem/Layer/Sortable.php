<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FilmSlider\Block\Adminhtml\SliderItem\Widget\Render\SliderItem\Layer;

use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Backend\Block\Widget;
use Magento\Backend\Block\Widget\Button;

class Sortable extends Widget implements RendererInterface
{

    const ELEMENT_NAME = 'layersortable';

    protected function _construct()
    {
        $this->setTemplate('TemplateMonster_FilmSlider::slideritem/widget/render/slideritem/layer/sortable.phtml');
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
