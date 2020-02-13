<?php

namespace TemplateMonster\ThemeOptions\Model\Config\Source;

class Size implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'auto',    'label' => __('Auto')],
            ['value' => 'contain', 'label' => __('Contain')],
            ['value' => 'cover',   'label' => __('Cover')]
        ];
    }
}

