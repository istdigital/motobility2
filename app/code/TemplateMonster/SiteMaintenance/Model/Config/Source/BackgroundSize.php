<?php

namespace TemplateMonster\SiteMaintenance\Model\Config\Source;

class BackgroundSize implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Auto')],
            ['value' => 1, 'label' => __('Contain')],
            ['value' => 2, 'label' => __('Cover')]
        ];
    }
}
