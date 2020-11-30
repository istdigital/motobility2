<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */

/**
 * Copyright © 2016 Amasty. All rights reserved.
 */

namespace Amasty\Preorder\Block\Product\View\Preorder;

class ProductAbstract extends \Magento\Catalog\Block\Product\View\AbstractView
{
    /**
     * @var \Amasty\Preorder\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var \Magento\CatalogInventory\Model\StockRegistry
     */
    protected $stockRegistry;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        \Amasty\Preorder\Helper\Data $helper,
        \Magento\CatalogInventory\Model\StockRegistry $stockRegistry,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $arrayUtils, $data);
        $this->jsonEncoder = $jsonEncoder;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * @return bool
     */
    public function canShowBlock()
    {
        return $this->helper->preordersEnabled();
    }

    /**
     * @return string
     */
    public function getCartLabel()
    {
        return $this->helper->getProductPreorderCartLabel($this->getProduct());
    }

    /**
     * @return string
     */
    public function getPreorderNote()
    {
        return $this->helper->getProductPreorderNote($this->getProduct());
    }

    /**
     * @return string
     */
    public function getPreorderNotePosition()
    {
        return $this->helper->getPreorderNotePosition();
    }

    /**
     * @return bool
     */
    public function isProductPreorder()
    {
        return $this->helper->getIsProductPreorder($this->getProduct());
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getOriginalNote()
    {
        return $this->getProduct()->isAvailable() ? __('In stock') : __('Out of stock');
    }
}
