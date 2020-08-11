<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */


/**
 * Copyright © 2016 Amasty. All rights reserved.
 */

namespace  Amasty\Preorder\Block\Checkout\Cart;

use Magento\Framework\View\Element\Template;

class Preorder extends \Magento\Framework\View\Element\Template
{
    const ITEM = 'item';
    /**
     * @var \Amasty\Preorder\Helper\Data
     */
    protected $helper;

    /**
     * Note constructor.
     * @param Template\Context $context
     * @param \Amasty\Preorder\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Amasty\Preorder\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    public function canShowBlock()
    {
        return $this->helper->preordersEnabled() && $this->helper->getQuoteItemIsPreorder($this->getItem());
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $quoteItem
     * @return $this
     */
    public function setItem(\Magento\Quote\Model\Quote\Item\AbstractItem $quoteItem)
    {
        $this->setData(static::ITEM, $quoteItem);
        return $this;
    }

    /**
     * @return \Magento\Quote\Model\Quote\Item\AbstractItem
     */
    public function getItem()
    {
        return $this->getData(static::ITEM);
    }

    /**
     * @return string
     */
    public function getPreorderNote()
    {
        return $this->helper->getQuoteItemPreorderNote($this->getItem());
    }
}
