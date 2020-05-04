<?php

namespace StripeIntegration\Payments\Controller\Customer;

use StripeIntegration\Payments\Helper\Logger;

class Cards extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $session,
        \StripeIntegration\Payments\Model\Config $config,
        \StripeIntegration\Payments\Helper\Generic $helper,
        \StripeIntegration\Payments\Model\StripeCustomer $stripeCustomer
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);

        $this->config = $config;
        $this->helper = $helper;
        $this->stripeCustomer = $stripeCustomer;

        if (!$session->isLoggedIn())
            $this->_redirect('customer/account/login');
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();

        if (isset($params['save']))
            return $this->saveCard($params);
        else if (isset($params['delete']))
            return $this->deleteCard($params['delete']);

        return $this->resultPageFactory->create();
    }

    public function saveCard($params)
    {
        try
        {
            if (empty($params['payment']) || empty($params['payment']['cc_stripejs_token']))
                throw new \Exception("Sorry, the card could not be saved. Unable to use Stripe.js.");

            $parts = explode(":", $params['payment']['cc_stripejs_token']);

            if (!$this->helper->isValidToken($parts[0]))
                throw new \Exception("Sorry, the card could not be saved. Unable to use Stripe.js.");

            try
            {
                $this->stripeCustomer->createStripeCustomerIfNotExists();
                $this->stripeCustomer->addCard($parts[0]);
                $this->helper->addSuccess(__("Card **** %1 was added successfully.", $parts[2]));
            }
            catch (\Exception $e)
            {
                $this->helper->logError($e->getMessage());
                $this->helper->addError("Could not add card: " . $e->getMessage());
            }
        }
        catch (\Stripe\Error\Card $e)
        {
            $this->helper->addError($e->getMessage());
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

        $this->_redirect('stripe/customer/cards');
    }

    public function deleteCard($token)
    {
        try
        {
            $card = $this->stripeCustomer->deleteCard($token);

            // In case we deleted a source
            if (isset($card->card))
                $card = $card->card;

            $this->helper->addSuccess(__("Card **** %1 has been deleted.", $card->last4));
        }
        catch (\Stripe\Error\Card $e)
        {
            $this->helper->addError($e->getMessage());
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

        $this->_redirect('stripe/customer/cards');
    }
}
