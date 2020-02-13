<?php

namespace TemplateMonster\SocialSharing\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * CompactMenuDirection source model.
 */
class CompactMenuDirection implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Normal')],
            ['value' => 1, 'label' => __('Up')],
            ['value' => -1, 'label' => __('Down')],
        ];
    }
}
