<?php

namespace Expert\Creatio\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;


class Data extends AbstractHelper
{
    public function getSystemConfig($path)
    {
        return $this->scopeConfig->getValue(
            "creatio/$path",
            ScopeInterface::SCOPE_STORE
        );
    }
}
