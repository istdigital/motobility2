<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation;

class UrlSettings
{
    public function getMultipleValueSeparator() {
        return '_';
    }

    public function getRangeSeparator() {
        return '-';
    }

    public function getReplaceableParameterPattern() {
        return '/__\d__/';
    }

    public function getRangeParameterPattern() {
        return '/(-?[\d\._]*)-(-?[\d\._]*)/';
    }

    public function getNumberPattern() {
        $number = '[\d\.]+';
        $placeholder = '__\d__';
        return "/(?:$number)|(?:$placeholder)/";
    }
}