<?php

namespace TemplateMonster\ThemeOptions\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Repeat
 *
 * @package TemplateMonster\ThemeOptions\Model\Config\Source
 */
class Repeat implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'repeat',    'label' => __('Repeat')],
            ['value' => 'no-repeat', 'label' => __('No Repeat')],
            ['value' => 'repeat-x',  'label' => __('Repeat X')],
            ['value' => 'repeat-y',  'label' => __('Repeat Y')]
        ];
    }
}