<?php

namespace Yereone\Testimonials\Model\Config\Source\Widget;

class OrderBy implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'creation_time', 'label' => __('Created At')],
            ['value' => 'rating', 'label' => __('Rating')]
        ];
    }
}
