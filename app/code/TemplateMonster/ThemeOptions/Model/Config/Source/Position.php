<?php

namespace TemplateMonster\ThemeOptions\Model\Config\Source;

class Position implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'left top',      'label' => __('Left Top')],
            ['value' => 'center top',    'label' => __('Center Top')],
            ['value' => 'right top',     'label' => __('Right Top')],
            ['value' => 'left center',   'label' => __('Left Center')],
            ['value' => 'center center', 'label' => __('Center Center')],
            ['value' => 'right center',  'label' => __('Right Center')],
            ['value' => 'left bottom',   'label' => __('Left Bottom')],
            ['value' => 'center bottom', 'label' => __('Center Bottom')],
            ['value' => 'right bottom',  'label' => __('Right Bottom')]
        ];
    }
}

