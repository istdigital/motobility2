<?php

namespace TemplateMonster\SiteMaintenance\Model\Config\Source;

class BackgroundRepeat implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Repeat')],
            ['value' => 1, 'label' => __('No Repeat')],
            ['value' => 2, 'label' => __('Repeat X')],
            ['value' => 3, 'label' => __('Repeat Y')]
        ];
    }
}
