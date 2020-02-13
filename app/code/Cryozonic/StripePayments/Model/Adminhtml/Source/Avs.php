<?php

namespace Cryozonic\StripePayments\Model\Adminhtml\Source;

class Avs
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
                'label' => __('Enabled')
            ],
        ];
    }
}
