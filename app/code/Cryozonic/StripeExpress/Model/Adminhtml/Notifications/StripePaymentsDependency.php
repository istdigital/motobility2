<?php

namespace Cryozonic\StripeExpress\Model\Adminhtml\Notifications;

class StripePaymentsDependency implements \Magento\Framework\Notification\MessageInterface
{
    public $requiredStripePaymentsVersion = "2.6.0";
    public $stripePaymentsLink = "https://store.cryozonic.com/magento-2/stripe-payments.html";
    public $stripePaymentsUpgradeLink = "https://store.cryozonic.com/documentation/magento-2-stripe-payments#upgrade";

    public function isStripePaymentsMissing()
    {
        if (!class_exists("\Cryozonic\StripePayments\Model\Config"))
            return true;

        return false;
    }

    public function areDependenciesUpdated()
    {
        $version = \Cryozonic\StripePayments\Model\Config::$moduleVersion;

        if ($version[0] == "{")
            return true;

        return version_compare($version, $this->requiredStripePaymentsVersion, ">=");
    }

    public function getIdentity()
    {
        return 'cryozonic_stripeexpress_notification_stripepayments_dependency';
    }

    public function isDisplayed()
    {
        return $this->isStripePaymentsMissing() || !$this->areDependenciesUpdated();
    }

    public function getText()
    {
        if ($this->isStripePaymentsMissing())
        {
            return "<strong>Stripe Payments is not installed</strong> - The Stripe Express add-on requires the core module <a href=\"{$this->stripePaymentsLink}\" target=\"_blank\">Stripe Payments v{$this->requiredStripePaymentsVersion}</a> to be installed, but an installation of the module was not found.";
        }

        if (!$this->areDependenciesUpdated())
        {
            return "<strong>Stripe Payments must be upgraded</strong> - The Stripe Express add-on requires <a href=\"{$this->stripePaymentsLink}\" target=\"_blank\">Stripe Payments v{$this->requiredStripePaymentsVersion}</a> or later to be installed, but an older version of the module was found instead. To upgrade, please refer to the <a href=\"{$this->stripePaymentsUpgradeLink}\" target=\"_blank\">upgrade instructions</a>.";
        }

        return null;
    }

    public function getSeverity()
    {
        // Possible values: SEVERITY_CRITICAL, SEVERITY_MAJOR, SEVERITY_MINOR, SEVERITY_NOTICE
        return self::SEVERITY_MAJOR;
    }
}
