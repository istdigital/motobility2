<?php
/** 
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core\Plugins;

use Magento\Catalog\Block\Product\ListProduct;
use Manadev\Core\Configuration;

class ProductListBlock {
    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration) {

        $this->configuration = $configuration;
    }
    public function afterToHtml(ListProduct $block, $html) {
        if ($this->configuration->isProductListSelectRenderedInHiddenDiv()) {
            $additionalHtml = <<<HTML
<div id="product_list_select" style="display: none; ">{$block->getLoadedProductCollection()->getSelect()->__toString()}</div>
HTML;
            return $additionalHtml . $html;
        }
        else {
            return $html;
        }
    }
}