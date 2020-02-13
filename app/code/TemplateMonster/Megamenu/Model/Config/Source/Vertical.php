<?php

namespace TemplateMonster\Megamenu\Model\Config\Source;

class Vertical implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Topnav')],
            ['value' => 1, 'label' => __('Sidebar')]
        ];
    }
}
