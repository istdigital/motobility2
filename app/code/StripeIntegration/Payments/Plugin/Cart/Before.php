<?php

namespace StripeIntegration\Payments\Plugin\Cart;

use StripeIntegration\Payments\Helper\Logger;

class Before
{
    public function __construct(
        \StripeIntegration\Payments\Helper\Subscriptions $subscriptionsHelper,
        \StripeIntegration\Payments\Helper\Generic $paymentsHelper,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable
    ) {
        $this->subscriptionsHelper = $subscriptionsHelper;
        $this->paymentsHelper = $paymentsHelper;
        $this->configurable = $configurable;
    }

    /**
     * beforeAddProduct
     *
     * @param      $subject
     * @param      $productInfo
     * @param null $requestInfo
     *
     * @return array
     * @throws LocalizedException
     */
    public function beforeAddProduct($subject, $productInfo, $requestInfo = null)
    {
        return [$productInfo, $requestInfo];
    }
}
