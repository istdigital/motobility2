<?php
/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\AjaxCompare\Helper\Product\Compare;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;

class AjaxResponse extends AbstractHelper
{

    /**
     * @var Data
     */
    protected $_helperData;


    /**
     * AjaxResponse constructor.
     * @param Context $context
     * @param Data $helperData
     */
    public function __construct(Context $context,
                                Data $helperData)
    {
        $this->_helperData = $helperData;
        $this->_scopeConfig = $context->getScopeConfig();
        parent::__construct($context);
    }

    /**
     * @param $subject
     * @param $message
     * @return mixed
     */
    public function getResult($subject, $message)
    {
        $response = $subject->getResponse();
        return $response->representJson($this->_helperData->jsonEncode($message));
    }

    /**
     * @return mixed
     */
    public function getModuleStatus()
    {
        $active = $this->_scopeConfig->getValue('ajaxcompare/config/ajaxcompare_product_active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return empty($active) ? 0 : $active;
    }
}
