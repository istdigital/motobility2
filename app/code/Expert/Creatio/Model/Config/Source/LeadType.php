<?php

namespace Expert\Creatio\Model\Config\Source;


use Expert\Creatio\Helper\Data;
use Expert\Creatio\Model\Creatio;

class LeadType implements \Magento\Framework\Option\ArrayInterface
{

    private $auth_url = null;
    private $auth_username = null;
    private $auth_password = null;

    public function __construct(Data $helper)
    {
        $this->auth_url = $helper->getSystemConfig('general/url');
        $this->auth_username = $helper->getSystemConfig('general/username');
        $this->auth_password = $helper->getSystemConfig('general/password');
    }
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $creatio = new Creatio($this->auth_url,$this->auth_username,$this->auth_password);
        return $creatio->getOptions('LeadType');
    }
}

