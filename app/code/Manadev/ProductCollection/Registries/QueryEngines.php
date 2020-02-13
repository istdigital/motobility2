<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\ProductCollection\Registries;

use Manadev\Core\Exceptions\InterfaceNotImplemented;
use Manadev\ProductCollection\Contracts\QueryEngine;

class QueryEngines {
    /**
     * @var QueryEngine[]
     */
    protected $queryEngines;
    public function __construct(array $queryEngines)
    {
        foreach ($queryEngines as $queryEngine) {
            if (!($queryEngine instanceof QueryEngine)) {
                throw new InterfaceNotImplemented(sprintf("'%s' does not implement '%s' interface.",
                    get_class($queryEngine), QueryEngine::class));
            }
        }
        $this->queryEngines = array_filter($queryEngines, function(QueryEngine $queryEngine) {
            return $queryEngine->isEnabled();
        });
    }

    /**
     * @param $name
     * @return bool|QueryEngine
     */
    public function get($name) {
        return isset($this->queryEngines[$name]) ? $this->queryEngines[$name] : false;
    }
}