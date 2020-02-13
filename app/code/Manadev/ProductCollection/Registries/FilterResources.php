<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\ProductCollection\Registries;

use Manadev\Core\Exceptions\InterfaceNotImplemented;
use Manadev\ProductCollection\Contracts\FilterResource;
use Manadev\ProductCollection\Contracts\SupportedFilters;

class FilterResources implements SupportedFilters {
    /**
     * @var FilterResource[]
     */
    protected $filterResources;

    public function __construct(array $filterResources)
    {
        foreach ($filterResources as $filterResource) {
            if (!($filterResource instanceof FilterResource)) {
                throw new InterfaceNotImplemented(sprintf("'%s' does not implement '%s' interface.",
                    get_class($filterResource), FilterResource::class));
            }
        }
        $this->filterResources = $filterResources;
    }

    /**
     * @param $name
     * @return bool|FilterResource
     */
    public function get($name) {
        return isset($this->filterResources[$name]) ? $this->filterResources[$name] : false;
    }

    /**
     * @return bool|FilterResource
     */
    public function getList() {
        return $this->filterResources;
    }
}