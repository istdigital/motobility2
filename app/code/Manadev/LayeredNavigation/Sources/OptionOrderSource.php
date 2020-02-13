<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation\Sources;

use Manadev\Core\Source;

class OptionOrderSource extends Source
{
    const POSITION = 'position';
    const NAME_ALPHABETIC = 'name_alphabetic';
    const NAME_NUMERIC = 'name_numeric';
    const COUNT = 'count';

    public function getOptions() {
        return [
            static::POSITION => __('Position'),
            static::NAME_ALPHABETIC => __('Name (Alphabetic)'),
            static::NAME_NUMERIC => __('Name (Numeric)'),
            static::COUNT => __('Count'),
        ];
    }
}