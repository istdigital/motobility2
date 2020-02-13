<?php
/** 
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core;

use Magento\Framework\App\RequestInterface;
use Manadev\Core\Contracts\PageType;
use Manadev\Core\Registries\PageTypes;

class Helper {
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    /**
     * @var PageTypes
     */
    protected $pageTypes;

    public function __construct(
        RequestInterface $request,
        PageTypes $pageTypes
    ) {
        $this->request = $request;
        $this->pageTypes = $pageTypes;
    }
    public function getCurrentRoute() {
        return str_replace('_', '/', strtolower($this->request->getFullActionName()));
    }

    /**
     * @param string|null $route
     * @return PageType
     */
    public function getPageType($route = null) {
        return $this->pageTypes->get($route ?: $this->getCurrentRoute());
    }

    public function decodeGridSerializedInput($encoded) {
        $result = array();
        parse_str($encoded, $decoded);
        foreach ($decoded as $key => $value) {
            $result[$key] = null;
            parse_str(base64_decode($value), $result[$key]);
        }

        return $result;
    }

    /**
     * @param mixed $a
     * @param mixed $b
     * @return mixed
     */
    public function merge($a, $b) {
        if (is_object($a)) {
            if (!is_object($b)) {
                return $a;
            }
            foreach ($b as $key => $value) {
                if (isset($a->$key)) {
                    $a->$key = $this->merge($a->$key, $value);
                }
                else {
                    $a->$key = $value;
                }
            }

            return $a;
        }
        elseif (is_array($a)) {
            if (!is_array($b)) {
                return $a;
            }
            foreach ($b as $key => $value) {
                if (is_numeric($key)) {
                    $a[$key] = $value;
                }
                if (isset($a[$key])) {
                    $a[$key] = $this->merge($a[$key], $value);
                }
                else {
                    $a[$key] = $value;
                }
            }

            return $a;
        }
        else {
            return $b;
        }
    }

    /**
     * @param \Generator|array $items
     * @param $chunkSize
     * @return \Generator
     */
    public function iterateInChunks($items, $chunkSize) {
        $chunk = [];
        $count = 0;

        foreach ($items as $item) {
            $chunk[] = $item;
            $count++;

            if ($count >= $chunkSize) {
                yield $chunk;
                $chunk = [];
                $count = 0;
            }
        }

        if ($count) {
            yield $chunk;
        }
    }

    /**
     * @param array $items
     * @param string $columnName
     * @return array
     */
    public function pluck($items, $columnName) {
        return array_map(function($item) use ($columnName) {
            return $item->$columnName;
        }, $items);
    }

    /**
     * @param $items
     * @param $maxCount
     * @return \Generator|array
     */
    public function iterateNoMoreThanNTimes($items, $maxCount) {
        $count = 0;
        foreach ($items as $item) {
            yield $item;

            $count++;
            if ($count >= $maxCount) {
                break;
            }
        }

    }

    public function groupBy($columnName, $items) {
        $result = [];

        foreach ($items as $item) {
            if (!isset($result[$item->$columnName])) {
                $result[$item->$columnName] = [$item];
            }
            else {
                $result[$item->$columnName][] = $item;
            }
        }

        return $result;
    }
}