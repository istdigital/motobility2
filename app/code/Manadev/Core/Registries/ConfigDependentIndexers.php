<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core\Registries;

use Magento\Framework\ObjectManagerInterface;
use Manadev\Core\Contracts\ConfigDependentIndexer;

class ConfigDependentIndexers
{
    /**
     * @var ConfigDependentIndexer
     */
    protected $indexers;
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    public function __construct(ObjectManagerInterface $objectManager, array $configDependentIndexers)
    {
        $this->objectManager = $objectManager;
        $this->indexers = [];

        foreach ($configDependentIndexers as $name => $indexer) {
            $this->indexers[$name] = $indexer;
        }
    }

    /**
     * @param $name
     * @return ConfigDependentIndexer
     */
    public function get($name) {
        return isset($this->indexers[$name]) ? $this->indexers[$name] : null;
    }

    /**
     * @return ConfigDependentIndexer[]
     */
    public function getList() {
        return $this->indexers;
    }
}