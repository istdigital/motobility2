<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core\Plugins;

use Closure;
use Magento\Config\Model\Config;
use Manadev\Core\Registries\ConfigDependentIndexers;

class ConfigModelPlugin
{
    /**
     * @var Config\Loader
     */
    protected $_configLoader;
    /**
     * @var ConfigDependentIndexers
     */
    protected $configDependentIndexers;

    public function __construct(Config\Loader $configLoader, ConfigDependentIndexers $configDependentIndexers)
    {
        $this->_configLoader = $configLoader;
        $this->configDependentIndexers = $configDependentIndexers;
    }

    public function aroundSave(Config $configModel, Closure $proceed) {
        $data = $configModel->getData();
        $oldConfig = $this->_getConfig($configModel, true);

        $returnValue = $proceed();

        $indexers = [];
        foreach ($this->configDependentIndexers->getList() as $indexer) {
            foreach ($this->getModifiedFields($data, $oldConfig) as $path) {
                if (isset($indexer->getUsedStoreConfigPaths()[$path])) {
                    $indexers[] = $indexer;
                    break;
                }
            }
        }

        foreach ($indexers as $indexer) {
            $indexer->reindexAll();
        }

        return $returnValue;
    }

    protected function _getConfig(Config $configModel, $full = true)
    {
        return $this->_configLoader->getConfigByPath(
            $configModel->getSection(),
            $configModel->getScope(),
            $configModel->getScopeId(),
            $full
        );
    }

    protected function getModifiedFields($data, $oldConfig) {
        foreach ($data['groups'] as $groupId => $group) {
            if (!isset($group['fields'])) {
                continue;
            }

            foreach ($group['fields'] as $fieldId => $field) {
                $path = "{$data['section']}/{$groupId}/{$fieldId}";

                if (isset($oldConfig[$path]) && $oldConfig[$path]['value'] == $field['value']) {
                    continue;
                }

                yield $path;
            }
        }
    }
}