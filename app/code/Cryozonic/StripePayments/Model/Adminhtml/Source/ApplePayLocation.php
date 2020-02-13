<?php

namespace Cryozonic\StripePayments\Model\Adminhtml\Source;

class ApplePayLocation
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 1,
                'label' => __('Inside the Stripe payment form')
            ],
            [
                'value' => 2,
                'label' => __('Above all payment methods')
            ],
        ];
    }
}
