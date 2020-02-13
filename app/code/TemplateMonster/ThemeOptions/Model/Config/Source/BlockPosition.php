<?php

namespace TemplateMonster\ThemeOptions\Model\Config\Source;

class BlockPosition implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'left',  'label' => __('Left')],
            ['value' => 'right', 'label' => __('Right')],
            ['value' => 'none',  'label' => __('None')],
            ['value' => 'auto',  'label' => __('Auto')]
        ];
    }
}

