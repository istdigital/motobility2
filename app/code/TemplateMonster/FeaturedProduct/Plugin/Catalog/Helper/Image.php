<?php
/**
 * Copyright © 2016 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace TemplateMonster\FeaturedProduct\Plugin\Catalog\Helper;

use Magento\Catalog\Helper\Image as HelperImage;

class Image
{
    /**
     * @var \Magento\Framework\Registry $coreRegistry
     */
    protected $_coreRegistry = null;

    public function __construct(
        \Magento\Framework\Registry $coreRegistry
    ) {

        $this->_coreRegistry = $coreRegistry;
    }


    public function beforeInit(HelperImage $subject, $product, $imageId, $attributes = [])
    {

        if($this->_coreRegistry->registry('featured_product')) {
            $attributes = array_merge($attributes, (array) $this->_coreRegistry->registry('featured_product'));
        }

        return [$product, $imageId, $attributes];
    }





}
