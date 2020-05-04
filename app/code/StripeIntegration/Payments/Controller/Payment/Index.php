<?php

namespace StripeIntegration\Payments\Controller\Payment;

use Magento\Framework\Exception\LocalizedException;
use StripeIntegration\Payments\Helper\Logger;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutHelper;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \StripeIntegration\Payments\Helper\Generic
     */
    protected $helper;

    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;

    /**
     * @var \Magento\Framework\DB\Transaction
     */
    protected $dbTransaction;

    /**
     * Payment constructor.
     *
     * @param \Magento\Framework\App\Action\Context       $context
     * @param \Magento\Framework\View\Result\PageFactory  $resultPageFactory
     * @param \Magento\Checkout\Helper\Data               $checkoutHelper
     * @param \Magento\Sales\Model\OrderFactory           $orderFactory
     * @param \StripeIntegration\Payments\Helper\Generic    $helper
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Framework\DB\Transaction           $dbTransaction
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \StripeIntegration\Payments\Helper\Generic $helper,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $dbTransaction
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);

        $this->checkoutHelper = $checkoutHelper;
        $this->orderFactory = $orderFactory;

        $this->helper = $helper;
        $this->invoiceService = $invoiceService;
        $this->dbTransaction = $dbTransaction;
    }

    public function execute()
    {
        $paymentMethodType = $this->getRequest()->getParam('payment_method');
        $this->session = $this->checkoutHelper->getCheckout();

        switch ($paymentMethodType) {
            case 'fpx':
                $this->returnFromPaymentMethodsAPI();
                break;
            default:
                $this->returnFromSourcesAPI();
                break;
        }
    }

    private function returnFromPaymentMethodsAPI()
    {
        $paymentIntentId = $this->getRequest()->getParam('payment_intent');
        $clientSecret = $this->getRequest()->getParam('payment_intent_client_secret');

        if (empty($paymentIntentId) || empty($clientSecret)) {
            $this->session->restoreQuote();
            $this->messageManager->addError(__('Something has gone wrong with your payment. Please contact us.'));
            $this->_redirect('checkout/cart');
            return;
        }

        // Security, the error message is a bit confusing on purpose
        if ($clientSecret !== $this->session->getStripePaymentsClientSecret()) {
            $this->session->restoreQuote();
            $this->messageManager->addError(__('Your session has expired.'));
            $this->_redirect('checkout/cart');
            return;
        }

        // Load Order
        $incrementId = $this->session->getLastRealOrderId();
        $order = $this->orderFactory->create()->loadByIncrementId($incrementId);
        if (!$order->getId()) {
            $this->checkoutHelper->getCheckout()->restoreQuote();
            $this->messageManager->addError(__('No order for processing found'));
            $this->_redirect('checkout/cart');
            return;
        }

        /** @var \Magento\Payment\Model\Method\AbstractMethod $method */
        $method = $order->getPayment()->getMethodInstance();

        // Retrieve source
        try
        {
            $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);
            if (!$paymentIntent || !isset($paymentIntent->id))
                throw new LocalizedException(__('The payment intent with ID %1 could not be retrieved from Stripe', $sourceId));
        }
        catch (\Exception $e)
        {
            $this->session->restoreQuote();
            $this->messageManager->addError(__('Could not retrieve payment details. Please contact us.'));
            $this->_redirect('checkout/cart');
            return;
        }

        // Finish payment by status
        switch ($paymentIntent->status) {
            case 'succeeded':
            case 'processing':
                // Redirect to Success page
                $this->checkoutHelper->getCheckout()->getQuote()->setIsActive(false)->save();
                $this->_redirect('checkout/onepage/success');
                break;
            default:
                $order->addStatusHistoryComment("Authorization failed.");
                $this->helper->cancelOrCloseOrder($order);
                $this->session->restoreQuote();
                $this->messageManager->addError(__('Payment failed.'));
                $this->_redirect('checkout/cart');
                break;
        }
    }

    private function returnFromSourcesAPI()
    {
        $this->session = $this->checkoutHelper->getCheckout();
        $sourceId = $this->getRequest()->getParam('source');
        $clientSecret = $this->getRequest()->getParam('client_secret');

        if (empty($sourceId) || empty($clientSecret)) {
            $this->session->restoreQuote();
            $this->messageManager->addError(__('Something has gone wrong with your payment. Please contact us.'));
            $this->_redirect('checkout/cart');
            return;
        }

        // Security, the error message is a bit confusing on purpose
        if ($clientSecret !== $this->session->getStripePaymentsClientSecret()) {
            $this->session->restoreQuote();
            $this->messageManager->addError(__('Your session has expired.'));
            $this->_redirect('checkout/cart');
            return;
        }

        // Load Order
        $incrementId = $this->session->getLastRealOrderId();
        $order = $this->orderFactory->create()->loadByIncrementId($incrementId);
        if (!$order->getId()) {
            $this->checkoutHelper->getCheckout()->restoreQuote();
            $this->messageManager->addError(__('No order for processing found'));
            $this->_redirect('checkout/cart');
            return;
        }

        /** @var \Magento\Payment\Model\Method\AbstractMethod $method */
        $method = $order->getPayment()->getMethodInstance();

        // Retrieve source
        try
        {
            $source = \Stripe\Source::retrieve($sourceId);
            if (!$source || !isset($source->id))
                throw new LocalizedException(__('The source with ID %1 could not be retrieved from Stripe', $sourceId));
        }
        catch (\Exception $e)
        {
            $this->session->restoreQuote();
            $this->messageManager->addError(__('Could not retrieve payment details. Please contact us.'));
            $this->_redirect('checkout/cart');
            return;
        }

        // Finish payment by status
        switch ($source->status) {
            case 'chargeable':
            case 'pending':
            case 'consumed':
                // Redirect to Success page
                $this->checkoutHelper->getCheckout()->getQuote()->setIsActive(false)->save();
                $this->_redirect('checkout/onepage/success');
                break;
            case 'failed':
            case 'canceled':
                $order->addStatusHistoryComment("Authorization failed.");
                $this->helper->cancelOrCloseOrder($order);
                $this->session->restoreQuote();
                $this->messageManager->addError(__('Payment failed.'));
                $this->_redirect('checkout/cart');
                break;
            default:
                $this->session->restoreQuote();
                $this->messageManager->addError(__('The payment was not authorized.'));
                $this->_redirect('checkout/cart');
        }
    }
}
