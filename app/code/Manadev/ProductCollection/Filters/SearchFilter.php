<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\ProductCollection\Filters;

use Manadev\ProductCollection\Contracts\Filter;

class SearchFilter extends Filter
{
    protected $text;

    public function getType() {
        return 'search';
    }

    public function addSearchText($text) {
        $this->text = trim($this->text . ' ' . $text);
    }

    /**
     * @return mixed
     */
    public function getText() {
        return $this->text;
    }


}