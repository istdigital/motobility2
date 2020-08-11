<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */


namespace Amasty\Preorder\Plugin\SeoRichData\Block;

class Product
{
    const PRE_ORDER = 'http://schema.org/PreOrder';

    /**
     * @var null|\Magento\Catalog\Model\Product
     */
    private $product = null;

    /**
     * @var \Amasty\Preorder\Helper\Data
     */
    private $helper;

    public function __construct(\Amasty\Preorder\Helper\Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param \Amasty\SeoRichData\Block\Product $subject
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return array
     */
    public function beforeGetAvailabilityCondition($subject, $product)
    {
        $this->product = $product;

        return [$product];
    }

    /**
     * @param \Amasty\SeoRichData\Block\Product $subject
     * @param string $result
     *
     * @return string
     */
    public function afterGetAvailabilityCondition($subject, $result)
    {
        if ($this->product && $this->helper->getIsProductPreorder($this->product)) {
            $result = self::PRE_ORDER;
        }

        return $result;
    }
}
