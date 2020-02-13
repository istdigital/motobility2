<?php

namespace TemplateMonster\Parallax\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Block item video format type.
 *
 * @package TemplateMonster\Parallax\Model\Config\Source
 */
class VideoFormat implements ArrayInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 0,
                'label' => __('Static Video'),
            ],
            [
                'value' => 1,
                'label' => __('YouTube Video'),
            ],
        ];
    }
}
