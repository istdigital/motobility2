<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FilmSlider\Block\Adminhtml\Slider\Edit\Tab\Slides\Column;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class Edit extends AbstractRenderer
{
    public function render(DataObject $row)
    {
        return '<a href='.$this->getUrl('filmslider/slideritem/edit', ['slideritem_id'=>$row->getId()]).'>edit</a>';
    }
}
