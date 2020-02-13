<?php

namespace Cryozonic\StripeExpress\Model;

use Cryozonic\StripeExpress\Api\ServiceInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Webapi\ServiceInputProcessor;
use Magento\Checkout\Api\Data\ShippingInformationInterfaceFactory;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\CouldNotSaveException;
use Cryozonic\StripePayments\Helper\Logger;

class Service implements ServiceInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $cart;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    private $checkoutHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Cryozonic\StripeExpress\Helper\Helper
     */
    private $helper;

    /**
     * @var \Cryozonic\StripePayments\Helper\Generic
     */
    private $stripeHelper;

    /**
     * @var \Cryozonic\StripePayments\Model\Config
     */
    private $config;

    /**
     * @var \Cryozonic\StripePayments\Model\StripeCustomer
     */
    private $stripeCustomer;

    /**
     * @var ServiceInputProcessor
     */
    private $inputProcessor;

    /**
     * @var \Magento\Quote\Api\Data\EstimateAddressInterfaceFactory
     */
    private $estimatedAddressFactory;

    /**
     * @var \Magento\Quote\Api\ShippingMethodManagementInterface
     */
    private $shippingMethodManager;

    /**
     * @var \Magento\Checkout\Api\ShippingInformationManagementInterface
     */
    private $shippingInformationManagement;

    /**
     * @var ShippingInformationInterfaceFactory
     */
    private $shippingInformationFactory;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var CartManagementInterface
     */
    private $quoteManagement;

    /**
     * @var OrderSender
     */
    private $orderSender;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * Service constructor.
     *
     * @param \Psr\Log\LoggerInterface                                     $logger
     * @param ScopeConfigInterface                                         $scopeConfig
     * @param StoreManagerInterface                                        $storeManager
     * @param \Magento\Framework\UrlInterface                              $urlBuilder
     * @param \Magento\Framework\Event\ManagerInterface                    $eventManager
     * @param \Magento\Checkout\Model\Cart                                 $cart
     * @param \Magento\Checkout\Helper\Data                                $checkoutHelper
     * @param \Magento\Customer\Model\Session                              $customerSession
     * @param \Magento\Checkout\Model\Session                              $checkoutSession
     * @param \Cryozonic\StripeExpress\Helper\Helper                       $helper
     * @param \Cryozonic\StripePayments\Helper\Generic                     $stripeHelper
     * @param \Cryozonic\StripePayments\Model\Config                       $config
     * @param \Cryozonic\StripePayments\Model\StripeCustomer               $stripeCustomer
     * @param ServiceInputProcessor                                        $inputProcessor
     * @param \Magento\Quote\Api\Data\EstimateAddressInterfaceFactory      $estimatedAddressFactory
     * @param \Magento\Quote\Api\ShippingMethodManagementInterface         $shippingMethodManager
     * @param \Magento\Checkout\Api\ShippingInformationManagementInterface $shippingInformationManagement
     * @param ShippingInformationInterfaceFactory                          $shippingInformationFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface                   $quoteRepository
     * @param CartManagementInterface                                      $quoteManagement
     * @param OrderSender                                                  $orderSender
     * @param ProductRepositoryInterface                                   $productRepository
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Cryozonic\StripeExpress\Helper\Helper $helper,
        \Cryozonic\StripePayments\Helper\Generic $stripeHelper,
        \Cryozonic\StripePayments\Model\Config $config,
        \Cryozonic\StripePayments\Model\StripeCustomer $stripeCustomer,
        ServiceInputProcessor $inputProcessor,
        \Magento\Quote\Api\Data\EstimateAddressInterfaceFactory $estimatedAddressFactory,
        \Magento\Quote\Api\ShippingMethodManagementInterface $shippingMethodManager,
        \Magento\Checkout\Api\ShippingInformationManagementInterface $shippingInformationManagement,
        ShippingInformationInterfaceFactory $shippingInformationFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        CartManagementInterface $quoteManagement,
        OrderSender $orderSender,
        ProductRepositoryInterface $productRepository,
        \Cryozonic\StripePayments\Model\PaymentIntent $paymentIntent,
        \Magento\Framework\DataObjectFactory $dataObjectFactory
    ) {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
        $this->eventManager = $eventManager;
        $this->cart = $cart;
        $this->checkoutHelper = $checkoutHelper;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->helper = $helper;
        $this->stripeHelper = $stripeHelper;
        $this->config = $config;
        $this->stripeCustomer = $stripeCustomer;
        $this->inputProcessor = $inputProcessor;
        $this->estimatedAddressFactory = $estimatedAddressFactory;
        $this->shippingMethodManager = $shippingMethodManager;
        $this->shippingInformationManagement = $shippingInformationManagement;
        $this->shippingInformationFactory = $shippingInformationFactory;
        $this->quoteRepository = $quoteRepository;
        $this->quoteManagement = $quoteManagement;
        $this->orderSender = $orderSender;
        $this->productRepository = $productRepository;
        $this->paymentIntent = $paymentIntent;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Return URL
     * @param mixed $address
     * @return string
     */
    public function estimate_cart($address)
    {
        try
        {
            $quote = $this->cart->getQuote();
            $rates = [];

            if (!$quote->isVirtual()) {
                // Set Shipping Address
                $shippingAddress = $this->helper->getShippingAddress($address);
                $quote->getShippingAddress()
                      ->addData($shippingAddress)
                      ->save();

                // $quote->getShippingAddress()
                //     ->setCollectShippingRates(true);

                $this->quoteRepository->save($quote);
                $address = $quote->getShippingAddress();

                /** @var \Magento\Quote\Api\Data\EstimateAddressInterface $estimatedAddress */
                $estimatedAddress = $this->estimatedAddressFactory->create();
                $estimatedAddress->setCountryId($address->getCountryId());
                $estimatedAddress->setPostcode($address->getPostcode());
                $estimatedAddress->setRegion((string)$address->getRegion());
                $estimatedAddress->setRegionId($address->getRegionId());

                $rates = $this->shippingMethodManager->estimateByAddress($quote->getId(), $estimatedAddress);

                $this->cart->save();
            }

            $shouldInclTax = $this->helper->shouldCartPriceInclTax($quote->getStore());
            $currency = $quote->getQuoteCurrencyCode();
            $result = [];
            foreach ($rates as $rate) {
                if ($rate->getErrorMessage()) {
                    continue;
                }

                $result[] = [
                    'id' => $rate->getCarrierCode() . '_' . $rate->getMethodCode(),
                    'label' => implode(' - ', [$rate->getCarrierTitle(), $rate->getMethodTitle()]),
                    //'detail' => $rate->getMethodTitle(),
                    'amount' => $this->helper->getAmountCents($shouldInclTax ? $rate->getPriceInclTax() : $rate->getPriceExclTax(), $currency)
                ];
            }

            return \Zend_Json::encode([
                "paymentIntent" => $this->paymentIntent->create()->getClientSecret(),
                "results" => $result
            ]);
        }
        catch (\Exception $e)
        {
            throw new CouldNotSaveException(__($e->getMessage()), $e);
        }
    }

    /**
     * Apply Shipping Method
     *
     * @param mixed $address
     * @param string|null $shipping_id
     *
     * @return string
     * @throws CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function apply_shipping($address, $shipping_id = null)
    {
        if (count($address) === 0) {
            $address = $this->helper->getDefaultShippingAddress();
        }

        $quote = $this->cart->getQuote();

        try {
            if (!$quote->isVirtual()) {
                // Set Shipping Address
                $shippingAddress = $this->helper->getShippingAddress($address);
                $shipping = $quote->getShippingAddress()
                      ->addData($shippingAddress);

                if ($shipping_id) {
                    // Set Shipping Method
                    $shipping->setShippingMethod($shipping_id)
                             ->setCollectShippingRates(true)
                             ->collectShippingRates();

                    $parts = explode('_', $shipping_id);
                    $carrierCode = array_shift($parts);
                    $methodCode = implode("_", $parts);

                    /** @var \Magento\Quote\Api\Data\AddressInterface $ba */
                    $shippingAddress = $this->inputProcessor->convertValue($shippingAddress, 'Magento\Quote\Api\Data\AddressInterface');

                    /** @var \Magento\Checkout\Api\Data\ShippingInformationInterface $shippingInformation */
                    $shippingInformation = $this->shippingInformationFactory->create();
                    $shippingInformation
                        // ->setBillingAddress($shippingAddress)
                        ->setShippingAddress($shippingAddress)
                        ->setShippingCarrierCode($carrierCode)
                        ->setShippingMethodCode($methodCode);

                    $this->shippingInformationManagement->saveAddressInformation($quote->getId(), $shippingInformation);

                    // Update totals
                    $quote->setTotalsCollectedFlag(false);
                    $quote->collectTotals();

                    $this->quoteRepository->save($quote);
                }
            }

            $this->cart->save();

            $result = $this->helper->getCartItems($quote);
            return \Zend_Json::encode([
                "paymentIntent" => $this->paymentIntent->create()->getClientSecret(),
                "results" => $result
            ]);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()), $e);
        }
    }

    public function set_billing_address($data)
    {
        try {
            $quote = $this->cart->getQuote();

            // Place Order
            $billingAddress = $this->helper->getBillingAddress($data);

            // Set Billing Address
            $quote->getBillingAddress()
                  ->addData($billingAddress);

            $quote->setTotalsCollectedFlag(false);
            $quote->save();
        }
        catch (\Exception $e)
        {
            throw new CouldNotSaveException(__($e->getMessage()), $e);
        }

        return \Zend_Json::encode([
            "paymentIntent" => $this->paymentIntent->create()->getClientSecret(),
            "results" => null
        ]);
    }

    /**
     * Place Order
     *
     * @param mixed $result
     *
     * @return string
     * @throws CouldNotSaveException
     */
    public function place_order($result)
    {
        $paymentMethod = $result['paymentMethod'];
        $paymentMethodId = $paymentMethod['id'];

        // At this point the PI is already captured, so we don't want to trigger any further quote updates
        $this->paymentIntent->stopUpdatesForThisSession = true;

        $quote = $this->cart->getQuote();

        try {
            // Create an Order ID for the customer's quote
            $quote->reserveOrderId()->save();

            // Place Order
            $billingAddress = $this->helper->getBillingAddress($paymentMethod['billing_details']);

            // Set Billing Address
            $quote->getBillingAddress()
                  ->addData($billingAddress);

            if (!$quote->isVirtual()) {
                // Set Shipping Address
                $shippingAddress = $this->helper->getShippingAddressFromResult($result);
                $shipping = $quote->getShippingAddress()
                                  ->addData($shippingAddress);

                // Set Shipping Method
                $shipping->setShippingMethod($result['shippingOption']['id'])
                         ->setCollectShippingRates(true);
            }

            // Update totals
            $quote->setTotalsCollectedFlag(false);
            $quote->collectTotals();

            if ($this->helper->useStoreCurrency()) {
                $amount = $quote->getGrandTotal();
                $currency = $quote->getQuoteCurrencyCode();
            } else {
                $amount = $quote->getBaseGrandTotal();
                $currency = $quote->getBaseCurrencyCode();
            }

            // For multi-stripe account configurations, load the correct Stripe API key from the correct store view
            $this->storeManager->setCurrentStore($quote->getStoreId());
            $this->config->initStripe();
            $this->config->addOn(
                \Cryozonic\StripeExpress\Model\Config::$moduleName,
                \Cryozonic\StripeExpress\Model\Config::$moduleVersion,
                \Cryozonic\StripeExpress\Model\Config::$moduleUrl
            );

            // Set Checkout Method
            if (!$this->customerSession->isLoggedIn()) {
                // Use Guest Checkout
                $quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_GUEST)
                      ->setCustomerId(null)
                      ->setCustomerEmail($quote->getBillingAddress()->getEmail())
                      ->setCustomerIsGuest(true)
                      ->setCustomerGroupId(\Magento\Customer\Api\Data\GroupInterface::NOT_LOGGED_IN_ID);
            } else {
                $quote
                    ->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_CUSTOMER);
            }

            $quote->getPayment()->setAdditionalInformation('token', $paymentMethodId);
            $quote->getPayment()->setAdditionalInformation('source_id', $paymentMethodId);

            if ($this->stripeCustomer->getStripeId()) {
                $quote->getPayment()->setAdditionalInformation('customer_stripe_id', $this->stripeCustomer->getStripeId());
                $quote->getPayment()->setAdditionalInformation('customer_email', $this->stripeCustomer->getCustomerEmail());
            }

            $quote->getPayment()->importData(['method' => 'cryozonic_stripe']);

            // Save Quote
            $quote->save();

            // Place Order
            $this->paymentIntent->setPaymentMethod($paymentMethodId);
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->quoteManagement->submit($quote);
            if ($order) {
                $this->eventManager->dispatch(
                    'checkout_type_onepage_save_order_after',
                    ['order' => $order, 'quote' => $quote]
                );

                if ($order->getCanSendNewEmailFlag()) {
                    try {
                        $this->orderSender->send($order);
                    } catch (\Exception $e) {
                        $this->logger->critical($e);
                    }
                }

                $this->checkoutSession
                    ->setLastQuoteId($quote->getId())
                    ->setLastSuccessQuoteId($quote->getId())
                    ->setLastOrderId($order->getId())
                    ->setLastRealOrderId($order->getIncrementId())
                    ->setLastOrderStatus($order->getStatus());
            }

            $this->eventManager->dispatch(
                'checkout_submit_all_after',
                [
                    'order' => $order,
                    'quote' => $quote
                ]
            );

            return \Zend_Json::encode([
                'redirect' => $this->urlBuilder->getUrl('checkout/onepage/success', ['_secure' => $this->stripeHelper->isSecure()])
            ]);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()), $e);
        }
    }

    /**
     * Add to Cart
     *
     * @param string $request
     * @param string|null $shipping_id
     *
     * @return string
     * @throws CouldNotSaveException
     */
    public function addtocart($request, $shipping_id = null)
    {
        $params = [];
        parse_str($request, $params);

        $productId = $params['product'];
        $related = $params['related_product'];

        if (isset($params['qty'])) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $filter = new \Zend_Filter_LocalizedToNormalized(
                ['locale' => $objectManager->create('Magento\Framework\Locale\ResolverInterface')->getLocale()]
            );
            $params['qty'] = $filter->filter($params['qty']);
        }

        $quote = $this->cart->getQuote();

        try {
            // Get Product
            $storeId = $this->storeManager->getStore()->getId();
            $product = $this->productRepository->getById($productId, false, $storeId);

            $this->eventManager->dispatch(
                'cryozonic_stripeexpress_before_add_to_cart',
                ['product' => $product, 'request' => $request]
            );

            // Check is update required
            $isUpdated = false;
            foreach ($quote->getAllItems() as $item) {
                if ($item->getProductId() == $productId) {
                    $item = $this->cart->updateItem($item->getId(), $params);
                    if ($item->getHasError()) {
                        throw new LocalizedException(__($item->getMessage()));
                    }

                    $isUpdated = true;
                    break;
                }
            }

            // Add Product to Cart
            if (!$isUpdated) {
                $item = $this->cart->addProduct($product, $params);
                if ($item->getHasError()) {
                    throw new LocalizedException(__($item->getMessage()));
                }

                if (!empty($related)) {
                    $this->cart->addProductsByIds(explode(',', $related));
                }
            }

            $this->cart->save();

            if ($shipping_id) {
                // Set Shipping Method
                if (!$quote->isVirtual()) {
                    // Set Shipping Method
                    $quote->getShippingAddress()->setShippingMethod($shipping_id)
                             ->setCollectShippingRates(true)
                             ->collectShippingRates();
                }
            }

            // Update totals
            $quote->setTotalsCollectedFlag(false);
            $quote->collectTotals();
            $quote->save();

            $result = $this->helper->getCartItems($quote);
            return \Zend_Json::encode([
                "paymentIntent" => $this->paymentIntent->create()->getClientSecret(),
                "results" => $result
            ]);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()), $e);
        }
    }

    /**
     * Get Cart Contents
     *
     * @return string
     * @throws CouldNotSaveException
     */
    public function get_cart()
    {
        $quote = $this->cart->getQuote();

        try {
            $result = $this->helper->getCartItems($quote);
            return \Zend_Json::encode($result);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()), $e);
        }
    }
}
