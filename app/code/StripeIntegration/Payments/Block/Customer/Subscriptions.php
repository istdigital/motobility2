<?php

namespace StripeIntegration\Payments\Block\Customer;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Element;
use StripeIntegration\Payments\Helper\Logger;

class Subscriptions extends \Magento\Framework\View\Element\Template
{
    public $customerCards = null;
    public $helper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = [],
        \StripeIntegration\Payments\Model\StripeCustomer $stripeCustomer,
        \StripeIntegration\Payments\Helper\Generic $helper,
        \StripeIntegration\Payments\Helper\Subscriptions $subscriptionsHelper
    ) {
        $this->stripeCustomer = $stripeCustomer;
        $this->helper = $helper;
        $this->subscriptionsHelper = $subscriptionsHelper;

        parent::__construct($context, $data);
    }

    public function getSubscriptions()
    {
        try
        {
            return $this->stripeCustomer->getSubscriptions();
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

    public function getSubscriptionCard($sub)
    {
        if (!empty($sub->default_payment_method->type) && $sub->default_payment_method->type == 'card')
            return $this->helper->convertPaymentMethodToCard($sub->default_payment_method);

        return null;
    }

    public function getSubscriptionCardId($sub)
    {
        $card = $this->getSubscriptionCard($sub);

        if ($card)
            return $card->id;
        else
            return null;
    }

    public function formatSubscriptionName($sub)
    {
        return $this->subscriptionsHelper->formatSubscriptionName($sub);
    }

    public function formatDelivery($sub)
    {
        $interval = $sub->plan->interval;
        $count = $sub->plan->interval_count;

        if ($count > 1)
            return "$count {$interval}s";
        else
            return ucfirst($interval);
    }

    public function formatLastBilled($sub)
    {
        $startDate = $sub->created;

        if (isset($sub->metadata["Trial"]))
        {
            $trialDays = $sub->metadata["Trial"];
            $startDate += (strtotime("+$trialDays") - time());
        }

        $date = $sub->current_period_start;

        if ($startDate > $date)
        {
            $day = date("j", $startDate);
            $sup = date("S", $startDate);
            $month = date("F", $startDate);

            return __("Trialing until %1<sup>%2</sup> %3", $day, $sup, $month);
        }
        else
        {
            $day = date("j", $date);
            $sup = date("S", $date);
            $month = date("F", $date);

            return "$day<sup>$sup</sup>&nbsp;$month";
        }
    }

    public function getCustomerCards()
    {
        if (isset($this->customerCards))
            return $this->customerCards;

        $this->customerCards = $this->stripeCustomer->getCustomerCards();

        if (empty($this->customerCards))
            $this->customerCards = []; // Set the variable to avoid unnecessary API calls

        return $this->customerCards;
    }

    public function getStatus($sub)
    {
        switch ($sub->status)
        {
            case 'trialing': // Trialing is not supported yet
            case 'active':
                return __("Active");
            case 'past_due':
                return __("Past Due");
            case 'unpaid':
                return __("Unpaid");
            case 'canceled':
                return __("Canceled");
            default:
                return __(ucwords(explode('_', $sub->status)));
        }
    }

    // Shipping Metadata strings
    protected static $first = "Shipping First Name";
    protected static $last = "Shipping Last Name";
    protected static $company = "Shipping Company";
    protected static $street = "Shipping Street";
    protected static $postcode = "Shipping Postcode";
    protected static $city = "Shipping City";
    protected static $country = "Shipping Country";
    protected static $region = "Shipping Region";
    protected static $telephone = "Shipping Telephone";

    public static function editableContent()
    {
        return [
            self::$first,
            self::$last,
            self::$company,
            self::$street,
            self::$postcode,
            self::$city,
            self::$telephone
        ];
    }

    public function getFormatedShippingLines($subscription)
    {
        $data = $subscription->metadata;

        $lines = [];

        // Name line
        if (!empty($data[self::$first]) && !empty($data[self::$last]))
            $name = $data[self::$first] . " " . $data[self::$last];
        else if (!empty($data[self::$first]))
            $name = $data[self::$first];
        else if (!empty($data[self::$last]))
            $name = $data[self::$last];
        else
            $name = "";

        if (!empty($name))
            $lines[] = $name;

        // Add the company if we have it
        if (!empty($data[self::$company]))
            $lines[] = $data[self::$company];

        // Street
        if (!empty($data[self::$street]))
            $lines[] = $data[self::$street];

        // City and postcode
        if (!empty($data[self::$city]) && !empty($data[self::$postcode]))
            $city = $data[self::$city] . " " . $data[self::$postcode];
        else if (!empty($data[self::$city]))
            $city = $data[self::$city];
        else if (!empty($data[self::$postcode]))
            $city = $data[self::$postcode];
        else
            $city = "";

        if (!empty($city))
            $lines[] = $city;

        // Region
        if (!empty($data[self::$region]))
            $lines[] = $data[self::$region];

        // Country
        if (!empty($data[self::$country]))
            $lines[] = $data[self::$country];

        // Telephone
        if (!empty($data[self::$telephone]))
            $lines[] = "Tel: " . $data[self::$telephone];

        return $lines;
    }

    public function hasEditableContent($subscription)
    {
        $lines = $this->getFormatedShippingLines($subscription);
        return !empty($lines);
    }
}
