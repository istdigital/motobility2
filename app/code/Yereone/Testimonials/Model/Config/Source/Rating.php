<?php

namespace Yereone\Testimonials\Model\Config\Source;

class Rating implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => __('')],
            ['value' => '20', 'label' => __('1 Star')],
            ['value' => '40', 'label' => __('2 Stars')],
            ['value' => '60', 'label' => __('3 Stars')],
            ['value' => '80', 'label' => __('4 Stars')],
            ['value' => '100', 'label' => __('5 Stars')],
        ];
    }
}
