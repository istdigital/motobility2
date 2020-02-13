<?php

namespace Cryozonic\StripePayments\Controller\Webhooks;

use Cryozonic\StripePayments\Helper\Logger;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

if (interface_exists("Magento\Framework\App\CsrfAwareActionInterface"))
    include __DIR__ . "/Index.m230.php";
else
    include __DIR__ . "/Index.m220.php";
