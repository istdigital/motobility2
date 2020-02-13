<?php

namespace TemplateMonster\SiteMaintenance\Model\Config\Source;

class BackgroundPosition implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Left Top')],
            ['value' => 1, 'label' => __('Center Top')],
            ['value' => 2, 'label' => __('Right Top')],
            ['value' => 3, 'label' => __('Left Center')],
            ['value' => 4, 'label' => __('Center Center')],
            ['value' => 5, 'label' => __('Right Center')],
            ['value' => 6, 'label' => __('Left Bottom')],
            ['value' => 7, 'label' => __('Center Bottom')],
            ['value' => 8, 'label' => __('Right Bottom')]
        ];
    }
}
