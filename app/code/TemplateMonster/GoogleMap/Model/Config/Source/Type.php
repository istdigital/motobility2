<?php

namespace TemplateMonster\GoogleMap\Model\Config\Source;

class Type implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'ROADMAP',   'label' => __('Roadmap')],
            ['value' => 'SATELLITE', 'label' => __('Satelite')],
            ['value' => 'HYBRID',    'label' => __('Hybrid')],
            ['value' => 'TERRAIN',   'label' => __('Terrain')]
        ];
    }
}

