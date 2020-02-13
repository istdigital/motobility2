<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation;

use Magento\Framework\App\RequestInterface;

class RequestParser {
    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var UrlSettings
     */
    protected $urlSettings;

    public function __construct(RequestInterface $request, UrlSettings $urlSettings) {
        $this->request = $request;
        $this->urlSettings = $urlSettings;
    }

    public function readMultipleValueInteger($paramName) {
        return $this->readMultipleValueIntegerString($this->request->getParam($paramName));
    }

    /**
     * @param string $values
     * @return string[]|bool
     */
    public function readMultipleValueIntegerString($values) {
        if (!$values) {
            return false;
        }

        if (is_array($values)) {
            return false;
        }

        $values = urldecode($values);
        $values = preg_replace($this->urlSettings->getReplaceableParameterPattern(), '', $values);

        $result = [];
        foreach (explode($this->urlSettings->getMultipleValueSeparator(), $values) as $value) {
            if ($value === false || $value === null || $value === '') {
                continue;
            }

            if (is_numeric($value)) {
                $result[] = $value;
            }
        }

        return count($result) ? $result : false;
    }

    public function readSingleValueInteger($paramName) {
        return $this->readSingleValueIntegerString($this->request->getParam($paramName));
    }

    /**
     * @param string $value
     * @return string|bool
     */
    public function readSingleValueIntegerString($value) {
        if (!$value) {
            return false;
        }

        if (is_array($value)) {
            return false;
        }

        $value = urldecode($value);
        $value = preg_replace($this->urlSettings->getReplaceableParameterPattern(), '', $value);
        if ($value === false || $value === null || $value === '') {
            return false;
        }

        return is_numeric($value) ? $value : false;
    }

    public function readMultipleValueRange($paramName) {
        return $this->readMultipleValueRangeString($this->request->getParam($paramName));
    }

    public function readMultipleValueRangeString($values, $keepPlaceholders = false) {
        if (!$values) {
            return false;
        }

        if (is_array($values)) {
            return false;
        }

        $values = urldecode($values);
        if ($keepPlaceholders) {
            $values = preg_replace_callback($this->urlSettings->getReplaceableParameterPattern(), function($matches) {
                return str_replace('_', '!', $matches[0]);
            }, $values);
        }
        else {
            $values = preg_replace($this->urlSettings->getReplaceableParameterPattern(), '', $values);
        }

        $result = [];
        $rangeRegex = $this->urlSettings->getRangeParameterPattern();
        foreach (explode($this->urlSettings->getMultipleValueSeparator(), $values) as $value) {
            if ($value === false || $value === null || $value === '') {
                continue;
            }

            if ($keepPlaceholders) {
                $value = str_replace('!', '_', $value);
            }

            if (preg_match($rangeRegex, $value, $matches)) {
                if ($matches[1] === '' || $matches[2] === '' || (float)$matches[1] <= (float)$matches[2]) {
                    $result[] = [$matches[1], $matches[2]];
                }
                else {
                    $result[] = [$matches[2], $matches[1]];
                }
            }
        }

        return count($result) ? $result : false;
    }
}