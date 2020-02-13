<?php

namespace TemplateMonster\GoogleMap\Model\Config\Source;

class Show implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'after_footer',  'label' => __('After footer')],
            ['value' => 'before_footer', 'label' => __('Before footer')]
        ];
    }
}

