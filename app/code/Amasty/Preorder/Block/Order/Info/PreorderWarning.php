<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */


/**
 * Copyright © 2016 Amasty. All rights reserved.
 */

namespace Amasty\Preorder\Block\Order\Info;

use Magento\Framework\View\Element\Template;

class PreorderWarning extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Amasty\Preorder\Helper\Data
     */
    protected $helper;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * Note constructor.
     * @param Template\Context $context
     * @param \Amasty\Preorder\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Amasty\Preorder\Helper\Data $helper,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->helper = $helper;
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getWarningText()
    {
        $order = $this->getOrder();
        if ($order instanceof \Magento\Sales\Model\Order == false) {
            return '[Please assign order parameter]';
        }

        if (!$this->helper->getOrderIsPreorderFlag($order)) {
            return '';
        }
        if($order->getState() == \Magento\Sales\Model\Order::STATE_COMPLETE) {
            return '';
        }

        return $this->helper->getOrderPreorderWarning($order->getId());
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }
}
