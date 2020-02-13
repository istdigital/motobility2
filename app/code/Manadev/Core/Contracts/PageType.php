<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */


namespace Manadev\Core\Contracts;

use Magento\Store\Model\Store;

abstract class PageType
{
    protected $route;

    /**
     * @return mixed
     */
    public function getRoute() {
        return $this->route;
    }

    /**
     * @param mixed $route
     */
    public function setRoute($route) {
        $this->route = $route;
    }

    /**
     * @param \Manadev\LayeredNavigation\Resources\Collections\FilterCollection $filters
     */
    abstract public function limitFilterCollection($filters);

    /**
     * @param Store $store
     * @return array
     */
    public function getSitemapItems($store) {
        return [];
    }

    /**
     * @return array
     */
    public function getUrlExtensions() {
        $result = [$this->getUrlExtension() => true];

        foreach ($this->getUrlExtensionHistory() as $extension) {
            if (!isset($result[$extension])) {
                $result[$extension] = false;
            }
        }

        return $result;
    }

    public function getUrlExtension() {
        return '';
    }

    public function getUrlExtensionHistory() {
        return [];
    }

    /**
     * @param \Manadev\Seo\Data\RouteData $route
     * @return array | null
     */
    public function getUrlKeySearchCondition($route) {
        return null;
    }

    abstract public function getConfigKey();
}