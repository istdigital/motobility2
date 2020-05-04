<?php

namespace StripeIntegration\Payments\Helper;

use Psr\Log\LoggerInterface;

class Logger
{
    static $logger = null;

    public static function getPrintableObject($obj)
    {
        if (!Logger::$logger)
            Logger::$logger = \Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface');

        if (is_object($obj))
        {
            if (method_exists($obj, 'debug'))
                $data = $obj->debug();
            else if (method_exists($obj, 'getData'))
                $data = $obj->getData();
            else
                $data = $obj;
        }
        else
            $data = $obj;

        return $data;
    }
    public static function debug($obj)
    {
        $data = Logger::getPrintableObject($obj);
        Logger::$logger->addDebug(print_r($data, true));
    }

    public static function log($obj)
    {
        $data = Logger::getPrintableObject($obj);
        Logger::$logger->addInfo(print_r($data, true));
    }
}
