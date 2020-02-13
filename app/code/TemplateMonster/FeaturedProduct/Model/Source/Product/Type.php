<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FeaturedProduct\Model\Source\Product;

class Type  implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => "new_product",          'label' => __('New Products')],
            ['value' => "sale_product",         'label' => __('Sale Products')],
            ['value' => "viewed_product",       'label' => __('Most Viewed Products')],
            ['value' => "bestseller_product",   'label' => __('Bestseller Products')],
            ['value' => "rated_product",        'label' => __('Top Rated Products')],
            ['value' => "manual_product",       'label' => __('Manual Products')],
            ['value' => "all_product",          'label' => __('All Products')]
        ];
    }

}