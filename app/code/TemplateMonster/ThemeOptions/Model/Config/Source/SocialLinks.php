<?php

namespace TemplateMonster\ThemeOptions\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Social links source model.
 *
 * @package TemplateMonster\ThemeOptions\Model\Config\Source
 */
class SocialLinks implements ArrayInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'image', 'label' => __('Image icon')],
            ['value' => 'font',  'label' => __('Font Icon')],
            ['value' => '0',   'label' => __('Disable')]
        ];
    }
}

