<?php

namespace StripeIntegration\Payments\Block\Customer;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Element;
use StripeIntegration\Payments\Helper\Logger;

class Cards extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = [],
        \StripeIntegration\Payments\Model\StripeCustomer $stripeCustomer,
        \StripeIntegration\Payments\Helper\Generic $helper,
        \Magento\Payment\Block\Form\Cc $ccBlock,
        \StripeIntegration\Payments\Model\Config $config
    ) {
        $this->stripeCustomer = $stripeCustomer;
        $this->helper = $helper;

        $this->ccBlock = $ccBlock;
        $this->config = $config;

        parent::__construct($context, $data);
    }

    public function getCards()
    {
        try
        {
            return $this->stripeCustomer->getCustomerCards();
        }
        catch (\Stripe\Error $e)
        {
            $this->helper->addError($e->getMessage());
            $this->helper->logError($e->getMessage());
            $this->helper->logError($e->getTraceAsString());
        }
        catch (\Exception $e)
        {
            $this->helper->addError($e->getMessage());
            $this->helper->logError($e->getMessage());
            $this->helper->logError($e->getTraceAsString());
        }
    }

    public function verifyBillingAddress()
    {
        $address = $this->helper->getCustomerDefaultBillingAddress();

        if (!$address || empty($address->getStreet()))
            return false;

        return true;
    }

    public function getBillingAddress()
    {
        return json_encode($this->helper->getStripeFormattedDefaultBillingAddress());
    }

    public function getCcMonths()
    {
        return $this->ccBlock->getCcMonths();
    }

    public function getCcYears()
    {
        return $this->ccBlock->getCcYears();
    }

    public function cardType($code)
    {
        return $this->helper->cardType($code);
    }
}
