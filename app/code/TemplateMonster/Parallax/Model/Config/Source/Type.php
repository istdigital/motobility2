<?php

namespace TemplateMonster\Parallax\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Block item type source model.
 *
 * @package TemplateMonster\Parallax\Model\Config\Source
 */
class Type implements ArrayInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 0,
                'label' => __('Background Image'),
            ],
            [
                'value' => 1,
                'label' => __('Background Video'),
            ],
            [
                'value' => 2,
                'label' => __('Image'),
            ],
            [
                'value' => 3,
                'label' => __('Text'),
            ],
        ];
    }
}
