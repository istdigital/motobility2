<?php

namespace StripeIntegration\Payments\Model\Method;

class Fpx extends \StripeIntegration\Payments\Model\Method\Api\PaymentMethods
{
    const METHOD_CODE = 'stripe_payments_fpx';

    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = self::METHOD_CODE;

    protected $type = 'fpx';

    public function createPaymentMethod()
    {
        $info = $this->getInfoInstance();

        return \Stripe\PaymentMethod::create([
            'type' => 'fpx',
            'fpx' => [
                'bank' => $info->getAdditionalInformation("bank")
            ],
            'billing_details' => $this->getBillingDetails()
        ]);
    }
}
