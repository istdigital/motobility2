<?php

namespace TemplateMonster\SiteMaintenance\Model\Config\Source;

class BackgroundAttachment implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Scroll')],
            ['value' => 1, 'label' => __('Fixed')]
        ];
    }
}
