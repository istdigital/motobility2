<?php

namespace Cryozonic\StripeExpress\Model;

use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Config\ValueHandlerPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Gateway\Validator\ValidatorPoolInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Api\Data\CartInterface;
use Cryozonic\StripePayments\Helper;
use Psr\Log\LoggerInterface;
use Magento\Framework\Validator\Exception;
use Cryozonic\StripePayments\Helper\Logger;
use Magento\Payment\Observer\AbstractDataAssignObserver;

class PaymentMethod extends \Magento\Payment\Model\Method\Adapter
{
    public static $code                 = "cryozonic_stripeexpress";

    // Fixes https://github.com/magento/magento2/issues/5413 in Magento 2.1
    public function setId($code) { }
    public function getId() { return $this::$code; }

    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return false;
    }
}
