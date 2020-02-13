<?php

namespace Cryozonic\StripePayments\Model\Adminhtml\Source;

class StripeJs
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 0,
                'label' => __('None')
            ),
            array(
                'value' => 1,
                'label' => __('Stripe.js v2')
            ),
            array(
                'value' => 2,
                'label' => __('Stripe.js v3 + Stripe Elements')
            ),
        );
    }
}
