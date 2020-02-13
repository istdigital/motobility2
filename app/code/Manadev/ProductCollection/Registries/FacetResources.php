<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\ProductCollection\Registries;

use Manadev\Core\Exceptions\InterfaceNotImplemented;
use Manadev\ProductCollection\Contracts\FacetResource;

class FacetResources {
    /**
     * @var FacetResource[]
     */
    protected $facetResources;

    public function __construct(array $facetResources)
    {
        foreach ($facetResources as $facetResource) {
            if (!($facetResource instanceof FacetResource)) {
                throw new InterfaceNotImplemented(sprintf("'%s' does not implement '%s' interface.",
                    get_class($facetResource), FacetResource::class));
            }
        }
        $this->facetResources = $facetResources;
    }

    /**
     * @param $name
     * @return bool|FacetResource
     */
    public function get($name) {
        return isset($this->facetResources[$name]) ? $this->facetResources[$name] : false;
    }

    /**
     * @return bool|FacetResource
     */
    public function getList() {
        return $this->facetResources;
    }
}