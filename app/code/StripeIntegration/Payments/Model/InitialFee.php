<?php
namespace StripeIntegration\Payments\Model;

use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Quote\Model\Quote;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote\Address\Total;

class InitialFee extends AbstractTotal
{
    public function __construct(
        \StripeIntegration\Payments\Helper\InitialFee $helper
    )
    {
        $this->helper = $helper;
        $this->setCode('initial_fee');
    }

    /**
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return $this
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        $items = $shippingAssignment->getItems();
        if (!count($items))
            return $this;

        $amount = $this->helper->getTotalInitialFeeForQuote($quote);
        if (is_numeric($quote->getBaseToQuoteRate()))
            $baseAmount = $amount / $quote->getBaseToQuoteRate();
        else
            $baseAmount = $amount;

        $total->addTotalAmount('initial_fee', $amount);
        $total->addBaseTotalAmount('base_initial_fee', $baseAmount);
        $total->setInitialFeeAmount($amount);
        $total->setBaseInitialFeeAmount($baseAmount);

        return $this;
    }

    /**
     * @param Total $total
     */
    protected function clearValues(Total $total)
    {
        $total->setTotalAmount('initial_fee', 0);
        $total->setBaseTotalAmount('base_initial_fee', 0);
        $total->setInitialFeeAmount(0);
        $total->setBaseInitialFeeAmount(0);
        $total->setGrandTotal(0);
        $total->setBaseGrandTotal(0);

        // $total->setTotalAmount('tax', 0);
        // $total->setBaseTotalAmount('base_tax', 0);
        // $total->setTotalAmount('discount_tax_compensation', 0);
        // $total->setBaseTotalAmount('base_discount_tax_compensation', 0);
        // $total->setTotalAmount('shipping_discount_tax_compensation', 0);
        // $total->setBaseTotalAmount('base_shipping_discount_tax_compensation', 0);
        // $total->setSubtotalInclTax(0);
        // $total->setBaseSubtotalInclTax(0);
    }

    /**
     * @param Quote $quote
     * @param Total $total
     * @return array
     */
    public function fetch(Quote $quote, Total $total)
    {
        return [
            'code' => $this->getCode(),
            'title' => 'Initial Fee',
            'value' => $this->helper->getTotalInitialFeeForQuote($quote)
        ];
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Initial Fee');
    }
}
