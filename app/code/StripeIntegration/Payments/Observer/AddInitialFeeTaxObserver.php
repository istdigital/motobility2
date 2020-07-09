<?php

namespace StripeIntegration\Payments\Observer;

use Magento\Framework\Event\ObserverInterface;
use StripeIntegration\Payments\Helper\Logger;

class AddInitialFeeTaxObserver implements ObserverInterface
{
    public function __construct(
        \StripeIntegration\Payments\Helper\InitialFee $helper,
        \StripeIntegration\Payments\Helper\Generic $paymentsHelper,
        \StripeIntegration\Payments\Model\Config $config
    )
    {
        $this->helper = $helper;
        $this->paymentsHelper = $paymentsHelper;
        $this->config = $config;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->config->isSubscriptionsEnabled())
            return $this;

        $total = $observer->getData('total');
        $quote = $observer->getData('quote');

        if ($total && $total->getInitialFeeAmount() > 0)
            $this->applyInitialFeeTax($quote, $total);

        return $this;
    }

    public function applyInitialFeeTax($quote, $total)
    {
        $baseExtraTax = 0;
        $extraTax = 0;

        foreach ($quote->getAllItems() as $item)
        {
            $appliedTaxes = $item->getAppliedTaxes();
            if (empty($appliedTaxes))
                continue;

            $product = $this->paymentsHelper->getSubscriptionProductFrom($item);
            $baseInitialFee = $product->getStripeSubInitialFee();

            if (empty($baseInitialFee) || !is_numeric($baseInitialFee) || $baseInitialFee <= 0)
                continue;

            $baseExtraTaxableAmount = $item->getQty() * $baseInitialFee;
            $taxPercent = $item->getTaxPercent();
            $baseExtraTax += round($baseExtraTaxableAmount * ($taxPercent / 100), 4);
        }

        $rate = $quote->getBaseToQuoteRate();
        $baseExtraTax = round($baseExtraTax, 2);
        $extraTax = round($baseExtraTax * $rate, 2);
        $total->addTotalAmount('tax', $extraTax);
        $total->addBaseTotalAmount('tax', $baseExtraTax);
        $total->setGrandTotal($total->getGrandTotal() + $extraTax);
        $total->setBaseGrandTotal($total->getBaseGrandTotal() + $baseExtraTax);
    }
}
