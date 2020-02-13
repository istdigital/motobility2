<?php

namespace Yereone\Testimonials\Model\Config\Source\Widget;

class ImageStyle implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'round', 'label' => __('Round ')],
            ['value' => 'square', 'label' => __('Square ')]
        ];
    }
}
