<?php

namespace TemplateMonster\ShopByBrand\Plugin\Catalog\Model;

use Magento\Catalog\Model\Product;

/**
 * Config edit plugin.
 *
 * @package TemplateMonster\ShopByBrand\Plugin\Catalog\Model
 */
class ProductPlugin
{

    /**
     * Retrieve Product URL
     *
     * @param  bool $useSid
     * @return string
     */
    public function aroundGetProductUrl(Product $subject, callable $proceed, $useSid = null)
    {
        $url = $proceed($useSid);
        $withoutProtocol = explode('://', $url);
        if(isset($withoutProtocol[1]) && strpos($withoutProtocol[1], '//')) {
            $protocol = $withoutProtocol[0];
            $newUrl = str_replace('//', '/', $withoutProtocol[1]);
            $url = $protocol . '://' . $newUrl;
        }
        return $url;
    }
}