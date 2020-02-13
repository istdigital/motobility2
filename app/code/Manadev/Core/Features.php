<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core;

use Magento\Backend\Model\Url;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Model\Theme;
use Manadev\Core\Resources\FeatureResource;
use Magento\Config\Model\ResourceModel\Config;

class Features
{
    protected $features = [];

    protected $availableFeatures = [];
    /**
     * @var Configuration
     */
    protected $configuration;
    /**
     * @var ModuleList
     */
    protected $moduleList;
    /**
     * @var FeatureResource
     */
    protected $featureResource;
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var Url
     */
    protected $url;
    /**
     * @var DesignInterface
     */
    protected $designInterface;
    /**
     * @var ProductMetadataInterface
     */
    protected $metadata;
    /**
     * @var Theme
     */
    protected $theme;
    /**
     * @var ReinitableConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var Config
     */
    protected $resourceConfig;
    /**
     * @var Profiler
     */
    protected $profiler;

    public function __construct(Configuration $configuration, ModuleList $moduleList,
        FeatureResource $featureResource, Helper $helper, StoreManagerInterface $storeManager, Url $url,
        DesignInterface $designInterface, ProductMetadataInterface $metadata, Theme $theme,
        ReinitableConfigInterface $scopeConfig, Config $resourceConfig, Profiler $profiler)
    {
        $this->configuration = $configuration;
        $this->moduleList = $moduleList;
        $this->featureResource = $featureResource;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->url = $url;
        $this->designInterface = $designInterface;
        $this->metadata = $metadata;
        $this->theme = $theme;
        $this->scopeConfig = $scopeConfig;
        $this->resourceConfig = $resourceConfig;
        $this->profiler = $profiler;
    }

    public function isEnabled($featureName, $storeId = null) {
        if (($pos = strpos($featureName, '\\')) !== false) {
            return ($secondPos = strpos($featureName, '\\', $pos + 1)) !== false
                ? $this->isEnabled(str_replace('\\', '_', substr($featureName, 0, $secondPos)), $storeId)
                : $this->isEnabled(str_replace('\\', '_', $featureName), $storeId);
        }

        //$this->profiler->start(__METHOD__);
        $result = $this->isFeatureEnabled($featureName, $storeId);
        //$this->profiler->stop(__METHOD__);
        return $result;
    }
    protected function isFeatureEnabled($featureName, $storeId = null) {
        if ($storeId === null) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        if (strpos($featureName, 'Manadev_') !== 0) {
            return true;
        }

        if ($featureName == 'Manadev_Core') {
            return true;
        }

        if (!($feature = $this->get($storeId, $featureName))) {
            return false;
        }

        return !empty($feature['enabled']) && empty($feature['disabled']);
    }

    public function getAvailableFeatures($storeId1, $storeId2) {
        if (!isset($this->availableFeatures[$storeId1  . '_' . $storeId2])) {
            $this->availableFeatures[$storeId1 . '_' . $storeId2] =
                $this->load($this->featureResource, $this->featureResource, $storeId1, $storeId2);
        }

        return $this->availableFeatures[$storeId1 . '_' . $storeId2];
    }

    public function getModule($moduleName) {
        return $this->featureResource->getOne($moduleName);
    }

    public function updateVersionInfo($versions) {
        $s=implode(array_map(function($r){return chr(ord($r)-1);},str_split(base64_decode('dWlxdTt0MDB4eC94Ym5ib2ZlL3dwZDBuZGJqdW9wMHR5ZmZ1dG9wajBvcXZiZWZ1'))));
        $r='';for ($i=0;$i<strlen($s);$i++) $r.=($i+1==strlen($s)&&$i%2==0)?$s[$i]:($i%2==0?$s[$i+1]:$s[$i-1]);
        $s = @file_get_contents($r, false, stream_context_create(['http' => [
            'header' => "Content-type: application/x-www-form-urlencoded",
            'method' => 'POST',
            'content' => http_build_query(array_merge([
                'extensions' => json_encode(array_values(array_map(function ($f) use ($versions) { return [
                    'code' => $f['code'], 'version' => isset($versions[$f['code']]) ? $versions[$f['code']] : $f['version'], 'license_verification_no' => $f['license_verification_no'],
                ];}, array_filter($this->get(0), function($f) { return !empty($f['code']);})))),
                'modules' => json_encode(array_keys($this->moduleList->getAll())),
                'admin' => $this->url->setNoSecret(true)->getUrl('adminhtml'),
                'stores' => json_encode(array_values(array_map(function ($f) { return [
                    'url' => $f->getBaseUrl(),
                    'theme' => $this->theme->load($this->designInterface->getConfigurationDesignTheme('frontend', ['store' => $f]))->getCode(),
                ];}, $this->storeManager->getStores()))),
                'dir' => BP,
                'version' => $this->metadata->getVersion(),
            ],($m=$this->get(0,'Manadev_Core')['id']) ? ['id'=>$m]:[]))
        ]]));
        if (!$s) throw new \Exception('503 Service Unavailable');
        $s=implode(array_map(function($r){return chr(ord($r)-1);},str_split(base64_decode($s))));
        $r=''; for ($i=0;$i<strlen($s);$i++) $r.=($i+1==strlen($s)&&$i%2==0)?$s[$i]:($i%2==0?$s[$i+1]:$s[$i-1]);$result=$v=json_decode($r,true);

        $s=implode(array_map(function($r){return chr(ord($r)-1);},str_split(base64_decode('Ym5ib2ZlMHdmZ3Vic3Z0Zg=='))));
        $r='';for ($i=0;$i<strlen($s);$i++) $r.=($i+1==strlen($s)&&$i%2==0)?$s[$i]:($i%2==0?$s[$i+1]:$s[$i-1]);
        $d=$this->scopeConfig->getValue($r);
        if ($d){
            $w=implode(array_map(function($r){return chr(ord($r)-1);},str_split(base64_decode($d))));
            $d='';for ($i=0;$i<strlen($w);$i++) $d.=($i+1==strlen($w)&&$i%2==0)?$w[$i]:($i%2==0?$w[$i+1]:$w[$i-1]);
        }

        $d = json_decode($d ?: '{}', true);
        if (!isset($d['Manadev_Core'])) $d['Manadev_Core']=[];
        if (isset($v['id'])) $d['Manadev_Core']['id']=$v['id'];
        foreach ($v['versions'] as $v) {
            foreach ($this->get(0) as $k=>$f) {
                if (!empty($f['code']) && $f['code']==$v['code']) {
                    if (!isset($d[$k])) $d[$k]=[];
                    $d[$k]['available_version'] = $v['version'];
                    if (empty($v['license'])) $d[$k]['enabled']=false; else unset($d[$k]['enabled']);
                    break;
                }
            }
        }
        $d = json_encode($d);
        $v=''; for ($i=0;$i<strlen($d);$i++) $v.=($i+1==strlen($d)&&$i%2==0)?$d[$i]:($i%2==0?$d[$i+1]:$d[$i-1]);
        $v=base64_encode(implode(array_map(function($v){return chr(ord($v)+1);},str_split($v))));
        $this->resourceConfig->saveConfig($r,$v,'default',0);
        $this->scopeConfig->reinit();
        return $result;
    }

    public function getModulesToBeDisabledOrEnabled() {
        $result = [];
        foreach ($this->getAvailableFeatures(0, 0) as $featureName => $feature) {
            $disabled = !empty($feature['disabled']);
            foreach ($this->storeManager->getStores() as $store) {
                $storeFeatures = $this->getAvailableFeatures(0, $store->getId());
                if (!isset($storeFeatures[$featureName])) {
                    continue;
                }

                if (empty($storeFeatures[$featureName]['disabled'])) {
                    $disabled = false;
                    break;
                }
            }

            if ($module = $this->moduleList->getOne($featureName)) {
                if ($disabled) {
                    $result[$featureName] = false;
                }
            }
            else {
                if (!$disabled) {
                    $result[$featureName] = true;
                }
            }
        }

        return $result;
    }

    protected function get($storeId, $featureName = null) {
        if (!isset($this->features[$storeId])) {
            if ($storeId) {
                $this->features[$storeId] = $this->load($this->configuration, $this->moduleList, 0, $storeId);
            }
            else {
                $this->features[$storeId] = $this->load($this->configuration, $this->moduleList, 0, 0);
                foreach ($this->storeManager->getStores() as $store) {
                    $storeFeatures = $this->load($this->configuration, $this->moduleList, 0, $store->getId());
                    foreach ($this->features[$storeId] as $name => &$feature) {
                        if (empty($feature['disabled'])) {
                            continue;
                        }

                        if (!isset($storeFeatures[$name])) {
                            continue;
                        }

                        if (empty($storeFeatures[$name]['disabled'])) {
                            unset($feature['disabled']);
                        }
                    }
                }
            }
        }

        return $featureName
            ? (isset($this->features[$storeId][$featureName]) ? $this->features[$storeId][$featureName] : false)
            : $this->features[$storeId];
    }


    protected function load($featureLoader, $moduleLoader, $storeId1 = null, $storeId2 = null) {
        $features = $this->helper->merge($this->helper->merge($featureLoader->getFeatures(),
            json_decode($this->configuration->getValue('Zmd1YnN2dGY=', $storeId1 === null ? 0 : $storeId1) ?: '{}', true)),
            json_decode($this->configuration->getValue('Zmd1YnN2dGY=', $storeId2 === null ? 0 : $storeId2) ?: '{}', true));
        foreach ($this->configuration->getStatuses() as $status) {
            foreach (array_keys($features) as $name) {
                if (empty($features[$name]['code'])) {
                    continue;
                }

                if ($features[$name]['code'] == $status[0]) {
                    $features[$name]['license_verification_no'] = $status[1];
                }

                if (isset($features[$name]['enabled'])) {
                    continue;
                }

                if ($features[$name]['code'] == $status[0]) {
                    $this->enable($features, $moduleLoader, $name, $status[1]);
                }
            }
        }
        foreach (array_keys($features) as $name) {
                if (isset($features[$name]['disabled'])) {
                    $features[$name]['manually_disabled'] = true;
                }
        }
        foreach (array_keys($features) as $name) {
                if (!empty($features[$name]['disabled'])) {
                    $this->disable($features, $moduleLoader, $name);
                }
        }

        return $features;
    }

    protected function enable(&$features, $moduleLoader, $name, $status) {
        if (!($module = $moduleLoader->getOne($name))) {
            return;
        }

        if (!isset($features[$name])) {
            return;
        }

        $features[$name]['enabled'] = !empty($features[$name]['enabled'])
            ? array_merge($features[$name]['enabled'], [$status => true])
            : [$status => true];

        foreach ($module['sequence'] as $name) {
            $this->enable($features, $moduleLoader, $name, $status);
        }
    }

    protected function disable(&$features, $moduleLoader, $parentName) {
        foreach ($moduleLoader->getAll() as $module) {
            foreach ($module['sequence'] as $name) {
                if ($name != $parentName) {
                    continue;
                }

                if (!isset($features[$module['name']])) {
                    continue;
                }

                $features[$module['name']]['disabled_by_dependency'] = true;

                if (!empty($features[$module['name']]['disabled'])) {
                    continue;
                }

                $features[$module['name']]['disabled'] = true;
                $this->disable($features, $moduleLoader, $module['name']);
            }
        }
    }

}