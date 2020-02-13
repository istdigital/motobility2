<?php

namespace Cryozonic\StripePayments\Model;

use Cryozonic\StripePayments\Api\ServiceInterface;
use Cryozonic\StripePayments\Helper\Logger;

class Service implements ServiceInterface
{
    /**
     * @var \Magento\Checkout\Helper\Data
     */
    private $checkoutHelper;

    /**
     * Constructor
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     */
    public function __construct(
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Cryozonic\StripePayments\Helper\Generic $helper,
        \Cryozonic\StripePayments\Model\PaymentIntent $paymentIntent
    ) {
        $this->checkoutHelper = $checkoutHelper;
        $this->paymentIntent = $paymentIntent;
        $this->helper = $helper;
    }

	/**
	 * Return URL
	 * @return string
	 */
    public function redirect_url()
    {
        return null;
    }

    public function get_payment_intent()
    {
        if (!$this->paymentIntent->create())
            throw new \Exception("The payment intent could not be created");

        return \Zend_Json::encode([
            "paymentIntent" => $this->paymentIntent->getClientSecret()
        ]);
    }
}
