<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
namespace Manadev\Core\Resources;

use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DataObject;
use Manadev\Core\Features;
use Exception;

class ExtensionCollection extends Collection
{
    protected $_store;
    /**
     * @var Features
     */
    protected $features;

    public function __construct(EntityFactoryInterface $entityFactory, Features $features)
    {
        parent::__construct($entityFactory);
        $this->features = $features;
    }

    public function setStore($store_id) {
        $this->_store = $store_id;
        return $this;
    }

    public function getStore() {
        return $this->_store;
    }

    protected function sort(&$features) {
        uasort($features, function($a, $b) {
            if (!isset($a['sort_order'])) return 1;
            if (!isset($b['sort_order'])) return -1;

            if (intval($a['sort_order']) < intval($b['sort_order'])) return -1;
            if (intval($a['sort_order']) > intval($b['sort_order'])) return 1;

            return 0;
        });
    }
    public function loadData($printQuery = false, $logQuery = false) {
        if ($this->_isCollectionLoaded) {
            return $this;
        }

        if(is_null($this->getStore())) {
            throw new Exception(__(sprintf("You must call setStore(...) before calling load() on %s objects.", get_class($this))));
        }

        $features = $this->getFeatures();

        foreach ($features as $extensionName => $extension) {
            if (!$this->isExtension($features, $extensionName)) {
                continue;
            }

            $version = $extension['version'];
            foreach ($features as $featureName => $feature) {
                if (!$this->isFeature($features, $extensionName, $featureName)) {
                    continue;
                }

                if (version_compare($version, $feature['version']) < 0) {
                    $version = $feature['version'];
                }
            }

            $this->addItem(new DataObject(array_merge($extension,
                ['name' => $extensionName, 'is_extension' => true, 'version' => $version])));

            foreach ($features as $featureName => $feature) {
                if (empty($feature['enabled']) || empty($feature['title'])) {
                    continue;
                }

                if (!$this->isFeature($features, $extensionName, $featureName)) {
                    continue;
                }

                $this->addItem(new DataObject(array_merge($feature, ['name' => $featureName,'version' => ''])));
            }
        }

        $this->_isCollectionLoaded = true;
        return $this;
    }

    protected function isFeature($features, $extensionName, $featureName) {
        if ($extensionName == $featureName) {
            return false;
        }

        if (!($module = $this->features->getModule($extensionName))) {
            return false;
        }

        if (!isset($features[$extensionName])) {
            return false;
        }

        foreach ($module['sequence'] as $name) {
            if ($name == $featureName) {
                return true;
            }
            if ($this->isFeature($features, $name, $featureName)) {
                return true;
            }
        }

        return false;
    }

    protected function getFeatures() {
        $features = $this->features->getAvailableFeatures(0, $this->getStore());
        if ($this->getStore()) {
            $localFeatures = $this->features->getAvailableFeatures($this->getStore(), $this->getStore());
            foreach ($features as $featureName => &$feature) {
                if (!isset($localFeatures[$featureName])) {
                    continue;
                }

                if (isset($localFeatures[$featureName]['disabled'])) {
                    $feature['locally_disabled'] = $localFeatures[$featureName]['disabled'];
                }

                if (!empty($localFeatures[$featureName]['manually_disabled'])) {
                    $feature['locally_manually_disabled'] = true;
                }
            }

            $globalFeatures = $this->features->getAvailableFeatures(0, 0);
            foreach ($features as $featureName => &$feature) {
                if (!isset($globalFeatures[$featureName])) {
                    continue;
                }

                if (!empty($globalFeatures[$featureName]['disabled'])) {
                    $feature['globally_disabled'] = true;
                }

                if (!empty($globalFeatures[$featureName]['manually_disabled'])) {
                    $feature['globally_manually_disabled'] = true;
                }
            }
        }
        $this->sort($features);

        return $features;
    }

    protected function isExtension($features, $featureName) {
        if (!isset($features[$featureName])) {
            return false;
        }

        $feature = $features[$featureName];
        if (empty($feature['code']) || empty($feature['enabled'])) {
            return false;
        }

        foreach (array_keys($feature) as $key) {
            if (strpos($key, 'removed_by_') !== 0) {
                continue;
            }

            if ($this->isExtension($features, substr($key, strlen('removed_by_')))) {
                return false;
            }
        }
        return true;

    }
}