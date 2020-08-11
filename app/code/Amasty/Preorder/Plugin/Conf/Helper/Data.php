<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */


namespace Amasty\Preorder\Plugin\Conf\Helper;

class Data
{
    /**
     * @var \Amasty\Preorder\Helper\Data
     */
    private $helper;

    /**
     * @var null|\Magento\Catalog\Model\Product
     */
    private $product = null;

    public function __construct(\Amasty\Preorder\Helper\Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param $subject
     * @param \Magento\Catalog\Model\Product $product
     * @param null $storeId
     *
     * @return array
     */
    public function beforeIsPreorderEnabled($subject, $product, $storeId = null)
    {
        $this->product = $product;

        return [$product, $storeId];
    }

    /**
     * @param $subject
     * @param bool $result
     *
     * @return bool
     */
    public function afterIsPreorderEnabled($subject, $result)
    {
        if ($result) {
            $result = $this->helper->getIsProductPreorder($this->product);
        }

        return $result;
    }
}
