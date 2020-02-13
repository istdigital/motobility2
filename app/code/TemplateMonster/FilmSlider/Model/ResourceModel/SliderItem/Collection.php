<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FilmSlider\Model\ResourceModel\SliderItem;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('TemplateMonster\FilmSlider\Model\SliderItem',
            'TemplateMonster\FilmSlider\Model\ResourceModel\SliderItem');
    }

    public function _applyBySliderFilter($slider)
    {
        $this->getSelect()
            ->where('parent_id = ?', $slider->getId())
            ->order('sort_item ASC')
            ->order('slideritem_id ASC');
        return $this;
    }
}
