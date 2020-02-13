<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation\Registries;

use Manadev\Core\Exceptions\InterfaceNotImplemented;
use Manadev\LayeredNavigation\Contracts\FilterType;

class FilterTypes {
    /**
     * @var FilterType[]
     */
    protected $filterTypes;

    public function __construct(array $filterTypes)
    {
        foreach ($filterTypes as $filterType) {
            if (!($filterType instanceof FilterType)) {
                throw new InterfaceNotImplemented(sprintf("'%s' does not implement '%s' interface.",
                    get_class($filterType), FilterType::class));
            }
        }
        $this->filterTypes = $filterTypes;
    }

    /**
     * @param $type
     * @return bool|FilterType
     */
    public function get($type) {
        return isset($this->filterTypes[$type]) ? $this->filterTypes[$type] : false;
    }

    public function getList() {
        return $this->filterTypes;
    }
}