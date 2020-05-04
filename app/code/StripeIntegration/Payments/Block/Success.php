<?php

namespace StripeIntegration\Payments\Block;

use StripeIntegration\Payments\Helper\Logger;

class Success extends \Magento\Checkout\Block\Onepage\Success
{
    /**
     * @var string
     */
    protected $_template = 'StripeIntegration_Payments::order/success.phtml';
    public $order = null;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \StripeIntegration\Payments\Helper\Generic $paymentsHelper,
        array $data = []
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_orderConfig = $orderConfig;
        $this->_isScopePrivate = true;
        $this->httpContext = $httpContext;

        parent::__construct($context, $checkoutSession, $orderConfig, $httpContext, $data);

        $this->pricingHelper = $pricingHelper;
        $this->checkoutHelper = $checkoutHelper;
        $this->paymentsHelper = $paymentsHelper;
        $this->order = $this->_checkoutSession->getLastRealOrder();
    }

    public function getBaseGrandTotal()
    {
        return $this->order->getBaseGrandTotal();
    }

    public function getFormattedGrandTotal()
    {
        return $this->paymentsHelper->addCurrencySymbol($this->order->getGrandTotal(), $this->order->getOrderCurrencyCode());
    }

    public function isWechatPaymentMethod()
    {
        return $this->order->getPayment()->getMethod() == "stripe_payments_wechat";
    }

    public function isSepaCreditPaymentMethod()
    {
        return $this->order->getPayment()->getMethod() == "stripe_payments_sepa_credit";
    }

    public function getRedirectUrl()
    {
        return $this->checkoutHelper->getCheckout()->getStripePaymentsRedirectUrl();
    }

    public function getSepaCreditBankName()
    {
        return $this->checkoutHelper->getCheckout()->getStripePaymentsSepaCreditBankName();
    }

    public function getSepaCreditIban()
    {
        return $this->checkoutHelper->getCheckout()->getStripePaymentsSepaCreditIban();
    }

    public function getSepaCreditBic()
    {
        return $this->checkoutHelper->getCheckout()->getStripePaymentsSepaCreditBic();
    }

    public function getOrderNumber()
    {
        return $this->order->getIncrementId();
    }
}
