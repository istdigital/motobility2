<?php

namespace StripeIntegration\Payments\Model\Adminhtml\Source;

class CcSave
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 0,
                'label' => __('Disabled')
            ],
            [
                'value' => 1,
                'label' => __('Ask the customer')
            ],
            [
                'value' => 2,
                'label' => __('Save without asking')
            ]
        ];
    }
}
