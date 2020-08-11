<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */


namespace Amasty\Preorder\Plugin;

use Amasty\Preorder\Block\Product\ListProduct\Preorder;
use \Magento\CatalogWidget\Block\Product\ProductsList as WidgetProductList;
use \Magento\Catalog\Block\Product\ListProduct as CatalogProductList;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;

abstract class AbstractProductList
{
    /**
     * @var \Amasty\Preorder\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var AbstractCollection|null
     */
    private $productCollection = null;

    public function __construct(
        \Amasty\Preorder\Helper\Data $helper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->helper = $helper;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param CatalogProductList | WidgetProductList $subject
     * @param string $resultHtml
     *
     * @return string
     */
    public function afterToHtml($subject, $resultHtml)
    {
        if ($this->helper->preordersEnabled() && !$this->isSkipAdvanedsearchPopup($subject)) {
            $productCollection = $this->getProductCollection($subject);
            
            $preOrderHtml = $subject->getLayout()
                ->createBlock(Preorder::class)
                ->setProductCollection($productCollection)
                ->toHtml();

            $resultHtml .= $preOrderHtml;
        }

        return $resultHtml;
    }

    /**
     * @param $subject
     *
     * @return AbstractCollection|null
     */
    private function getProductCollection($subject)
    {
        if ($this->productCollection === null) {
            $this->productCollection = $subject->getLoadedProductCollection();
        }

        return $this->productCollection;
    }

    /**
     * @param $productCollection
     *
     * @return $this
     */
    protected function setProductCollection($productCollection)
    {
        $this->productCollection = $productCollection;

        return $this;
    }

    /**
     * @param CatalogProductList | WidgetProductList $subject
     * @return bool
     */
    private function isSkipAdvanedsearchPopup($subject)
    {
        $result = $subject instanceof \Amasty\Xsearch\Block\Search\Product;

        /*  preorder is not working with advanced search popup anyway.
        This assertion prevents elastic response slowing down for popup. Uncomment after implementing compatibility */
//        $result = $result && !$this->scopeConfig->getValue('amasty_xsearch/product/add_to_cart');
        return $result;
    }
}
