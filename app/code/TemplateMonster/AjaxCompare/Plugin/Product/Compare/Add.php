<?php
/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\AjaxCompare\Plugin\Product\Compare;

use TemplateMonster\AjaxCompare\Helper\Product\Compare\AjaxResponse;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class Add
 *
 * @package TemplateMonster\AjaxCompare\Plugin\Product\Compare
 */
class Add
{
    /**
     * @var AjaxResponse
     */
    protected $_helper;

    /**
     * @var ManagerInterface
     */
    protected $_messageManager;

    /**
     * Add constructor.
     * @param AjaxResponse $helper
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        AjaxResponse $helper,
        ManagerInterface $messageManager
    )
    {
        $this->_helper = $helper;
        $this->_messageManager = $messageManager;
    }


    /**
     * @param $subject
     * @param \Closure $proceed
     * @return mixed
     */
    public function aroundExecute($subject, \Closure $proceed)
    {
        $request = $subject->getRequest();

        $returnValue = $proceed();
        $productName = $this->_messageManager->getMessages(true)->getLastAddedMessage()->getData();
        $message = __('You added product %1 to the comparison list.', $productName['product_name']);
        if ($request->isXmlHttpRequest()) {
            return $this->_helper->getResult($subject, [
                'success' => '1',
                'message' => $message
            ]);
        }
        return $returnValue;
    }
}
