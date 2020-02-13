<?php

namespace TemplateMonster\ThemeOptions\Model\Config\Source;

class Attachment implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'scroll', 'label' => __('Scroll')],
            ['value' => 'fixed',  'label' => __('Fixed')]
        ];
    }
}

