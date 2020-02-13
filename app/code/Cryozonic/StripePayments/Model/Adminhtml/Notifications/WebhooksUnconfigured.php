<?php

namespace Cryozonic\StripePayments\Model\Adminhtml\Notifications;

class WebhooksUnconfigured implements \Magento\Framework\Notification\MessageInterface
{
    public $stripeWebhooksConfigurationLink = "https://store.cryozonic.com/documentation/magento-2-stripe-payments#webhooks";

    public function __construct(
        \Cryozonic\StripePayments\Logger\Handler $logHandler
    ) {
        $this->logHandler = $logHandler;
    }

    public function getIdentity()
    {
        return 'cryozonic_stripepayments_notification_webhooks_unconfigured';
    }

    public function isDisplayed()
    {
        return !$this->logHandler->exists();
    }

    public function getText()
    {
        return "<strong>Stripe Webhooks have not yet been configured</strong> -
    Please refer to the <a href=\"{$this->stripeWebhooksConfigurationLink}\" target=\"_blank\">documentation</a> for instructions on how to set up your Stripe account.";
    }

    public function getSeverity()
    {
        // SEVERITY_CRITICAL, SEVERITY_MAJOR, SEVERITY_MINOR, SEVERITY_NOTICE
        return self::SEVERITY_MAJOR;
    }
}
