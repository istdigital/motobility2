<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core\Resources;

class FeatureResource
{
    protected $features;
    protected $modules;
    /**
     * @var string[]
     */
    protected $featureGlobs;
    /**
     * @var string[]
     */
    protected $moduleGlobs;

    public function __construct($featureGlobs = null, $moduleGlobs = null) {
        $this->featureGlobs = $featureGlobs;
        $this->moduleGlobs = $moduleGlobs;
    }

    protected function getFeatureGlobs() {
        if (!$this->featureGlobs) {
            $this->featureGlobs = array(
                BP . '/app/code/Manadev/*/etc/config.xml',
                BP . '/vendor/manadev/*/etc/config.xml',
                BP . '/vendor/manadev/*/app/code/Manadev/*/etc/config.xml',
            );
        }

        return $this->featureGlobs;
    }

    protected function getModuleGlobs() {
        if (!$this->moduleGlobs) {
            $this->moduleGlobs = array(
                BP . '/app/code/Manadev/*/etc/module.xml',
                BP . '/vendor/manadev/*/etc/module.xml',
                BP . '/vendor/manadev/*/app/code/Manadev/*/etc/module.xml',
            );
        }

        return $this->moduleGlobs;
    }

    public function getFeatures() {
        if (!$this->features) {
            $this->features = array();
            foreach ($this->getFeatureGlobs() as $glob) {
                foreach(glob($glob) as $path) {
                    $this->loadFeature($path);
                }
            }
        }

        return $this->features;
    }

    protected function loadFeature($path) {
        if (!file_exists($path)) {
            return;
        }

        if (($xml = simplexml_load_string(file_get_contents($path))) === false) {
            return;
        }

        if (!isset($xml->default->manadev_features)) {
            return;
        }

        foreach ($xml->default->manadev_features->children() as $featureXml) {
            $name = $featureXml->getName();
            $data = array();
            foreach ($featureXml->children() as $fieldXml) {
                $data[$fieldXml->getName()] = (string)$fieldXml;
            }
            $this->features[$name] = isset($this->features[$name])
                ? array_merge($this->features[$name], $data)
                : $data;
        }
    }

    public function getAll() {
        if (!$this->modules) {
            $this->modules = array();
            foreach ($this->getModuleGlobs() as $glob) {
                foreach (glob($glob) as $path) {
                    $this->loadModule($path);
                }
            }
        }

        return $this->modules;
    }

    protected function loadModule($path) {
        if (!file_exists($path)) {
            return;
        }

        if (($xml = simplexml_load_string(file_get_contents($path))) === false) {
            return;
        }

        if (!isset($xml->module['name'])) {
            return;
        }

        if (!isset($xml->module->sequence)) {
            return;
        }

        $name = (string)$xml->module['name'];
        $sequence = array();
        foreach ($xml->module->sequence->children() as $sequenceXml) {
            if (!isset($sequenceXml['name'])) {
                continue;
            }

            $sequence[] = (string)$sequenceXml['name'];
        }
        $this->modules[$name] = compact('name', 'sequence');
    }

    public function getOne($name) {
        $data = $this->getAll();
        return isset($data[$name]) ? $data[$name] : null;
    }
}