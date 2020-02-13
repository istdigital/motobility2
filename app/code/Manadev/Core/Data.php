<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core;

use Magento\Framework\App\ObjectManager;

class Data
{
    public static $_getter;

    public function __construct($data = []) {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    public function __get($key) {
        if (!static::$_getter) {
            return null;
        }

        return ObjectManager::getInstance()->get(static::$_getter)->$key($this);
    }

    public static function merge($target, ...$sources) {
        foreach ($sources as $source) {
            $target = static::mergeFromSource($target, $source);
        }

        return $target;
    }

    protected static function mergeFromSource($target, $source) {
        if (is_object($target)) {
            return static::mergeIntoObject($target, $source);
        }
        elseif (is_array($target)) {
            return static::mergeIntoArray($target, $source);
        }
        else {
            return $source;
        }
    }

    protected static function mergeIntoObject($target, $source) {
        foreach ($source as $key => $value) {
            if (isset($target->$key)) {
                $target->$key = static::merge($target->$key, $value);
            }
            else {
                $target->$key = $value;
            }
        }

        return $target;
    }

    protected static function mergeIntoArray($target, $source) {
        foreach ($source as $key => $value) {
            if (is_numeric($key)) {
                $target[] = $value;
            }
            elseif (isset($target[$key])) {
                $target[$key] = static::merge($target[$key], $value);
            }
            else {
                $target[$key] = $value;
            }
        }

        return $target;
    }
}