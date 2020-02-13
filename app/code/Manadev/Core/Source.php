<?php
/** 
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core;

abstract class Source implements \Magento\Framework\Data\OptionSourceInterface{

    abstract public function getOptions();

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: [ ['value' => '<value>', 'label' => '<label>'], ...]
     */
    public function toOptionArray() {
        $result = [];
        foreach($this->getOptions() as $value => $label) {
            $result[] = ['value' => $value, 'label' => $label];
        }
        return $result;
    }
}