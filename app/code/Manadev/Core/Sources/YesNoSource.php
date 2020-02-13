<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core\Sources;

use Manadev\Core\Source;

class YesNoSource extends Source
{
    public function getOptions() {
        return [
            1 => __('Yes'),
            0 => __('No'),
        ];
    }
}