<?php

namespace TemplateMonster\SiteMaintenance\Model\Config\Source;

class BackgroundType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Color')],
            ['value' => 1, 'label' => __('Image')]
        ];
    }
}
