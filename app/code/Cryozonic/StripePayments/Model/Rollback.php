<?php

namespace Cryozonic\StripePayments\Model;

use Cryozonic\StripePayments\Helper\Logger;

class Rollback extends \Magento\Framework\Model\AbstractModel
{
    public $entries;

    public function __construct(
        \Cryozonic\StripePayments\Helper\Generic $helper
    )
    {
        $this->entries = array();
        $this->helper = $helper;
    }

    public function addCharge($charge)
    {
        if (!isset($this->entries['charges']))
            $this->entries['charges'] = array();

        $this->entries['charges'][] = $charge;
    }

    public function addSubscription($subscription)
    {
        if (!isset($this->entries['subscriptions']))
            $this->entries['subscriptions'] = array();

        $this->entries['subscriptions'][] = $subscription;
    }

    public function run($msg = null, $e = null)
    {
        if (!$msg)
            $msg = __("Sorry, we could not complete the checkout process. Please contact us for more help.");

        // Refund the order charge
        if (isset($this->entries['charges']))
        {
            foreach ($this->entries['charges'] as $charge)
                $charge->refund();
        }

        // Unsubscribe the customer from all new subscriptions
        if (isset($this->entries['subscriptions']))
        {
            foreach ($this->entries['subscriptions'] as $subscription)
                $subscription->cancel();
        }

        // Stop the Magento checkout
        $this->helper->dieWithError($msg, $e);
    }
}
