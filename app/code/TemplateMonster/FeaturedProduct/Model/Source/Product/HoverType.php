<?php

namespace TemplateMonster\FeaturedProduct\Model\Source\Product;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class HoverType
 *
 * @package TemplateMonster\ThemeOptions\Model\Config\Source
 */
class HoverType implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0,          'label' => __('Default')],
            ['value' => 'switcher', 'label' => __('Switch image')],
            ['value' => 'carousel', 'label' => __('Image carousel')],
            ['value' => 'gallery',  'label' => __('Thumbnail gallery')]
        ];
    }
}