<?php

namespace Yereone\Testimonials\Model\Config\Source\Widget;

class Layout implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'slider', 'label' => __('Slider')],
            ['value' => 'grid', 'label' => __('Grid')]
        ];
    }
}
