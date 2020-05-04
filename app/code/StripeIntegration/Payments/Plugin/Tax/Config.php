<?php

namespace StripeIntegration\Payments\Plugin\Tax;

class Config
{
    // Disabled constructor:
    // Loading the product or cart while switching store currency seems to create an infinite recursion.
    // We are disabling the check and forcing CALC_ROW_BASE for now until a solution is found.

    // public function __construct(
    //     \StripeIntegration\Payments\Helper\Generic $helper
    // )
    // {
    //     $this->helper = $helper;
    // }

    public function aroundGetAlgorithm(
        $subject,
        \Closure $proceed,
        $storeId = null
    ) {
        $algorithm = $proceed($storeId);

        // If the order includes subscriptions, we need to overwrite the tax calculation algorithm to Unit,
        // because tax is calculated on a per-subscription basis
        if ($algorithm != \Magento\Tax\Model\Calculation::CALC_ROW_BASE /* && $this->helper->hasSubscriptions() */)
            return \Magento\Tax\Model\Calculation::CALC_ROW_BASE;

        return $algorithm;
    }
}
