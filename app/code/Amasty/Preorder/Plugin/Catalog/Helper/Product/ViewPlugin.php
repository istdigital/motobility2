<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */


declare(strict_types=1);

namespace Amasty\Preorder\Plugin\Catalog\Helper\Product;

use Magento\Catalog\Helper\Product\View as MagentoView;
use Magento\Framework\View\Result\Page as ResultPage;

class ViewPlugin
{
    const PREORDER_HANDLE = 'amasty_preorder_product';

    /**
     * @var \Amasty\Preorder\Helper\Data
     */
    private $helper;

    public function __construct(
        \Amasty\Preorder\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param MagentoView $subject
     * @param ResultPage $resultPage
     * @param $product
     * @param null $params
     * @return array
     */
    public function beforeInitProductLayout(
        MagentoView $subject,
        ResultPage $resultPage,
        $product,
        $params = null
    ) {
        if ($this->helper->getIsProductPreorder($product)) {
            $resultPage->addHandle(self::PREORDER_HANDLE);
        }

        return [$resultPage, $product, $params];
    }
}
