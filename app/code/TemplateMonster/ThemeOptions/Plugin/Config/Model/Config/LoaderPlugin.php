<?php

namespace TemplateMonster\ThemeOptions\Plugin\Config\Model\Config;

use Magento\Framework\App\RequestInterface;
use TemplateMonster\ThemeOptions\Helper\Data as ThemeOptionsHelper;

class LoaderPlugin
{
    protected $_helper;

    protected $_request;

    protected $_dataObjectFactory;

    public function __construct(ThemeOptionsHelper $helper, RequestInterface $request, \Magento\Framework\DataObjectFactory $dataObjectFactory)
    {
        $this->_helper = $helper;
        $this->_request = $request;
        $this->_dataObjectFactory = $dataObjectFactory;
    }

    public function aroundGetConfigByPath($subject, \Closure $proceed, $path, $scope, $scopeId, $full = true)
    {
        $config = $proceed($path, $scope, $scopeId, $full);
        if ($this->_isThemeOptionsConfig($path)) {
            $config = $this->_replaceColorSchemeSettings($config);
        }

        return $config;
    }

    protected function _isThemeOptionsConfig($path)
    {
        return $path === ThemeOptionsHelper::XML_MODULE_PREFIX;
    }

    protected function _replaceColorSchemeSettings($config)
    {
        $colorScheme = $this->_getColorScheme($config);

        foreach ($config as $path => $value) {
            if ($this->_isColorSettingsValue($path)) {
                if ($newPath = $this->_getNewPath($path, $colorScheme)) {
                    $config[$newPath] = $value;
                }
            }
        }

        return $config;
    }

    protected function _isColorSettingsValue($path)
    {
        if ($path === ThemeOptionsHelper::XML_PATH_COLOR_SCHEME) {
            return false;
        }

        return strpos($path, ThemeOptionsHelper::XML_PATH_COLOR_SETTING_GROUP) === 0;
    }

    protected function _getNewPath($path, $colorScheme)
    {
        $path = explode('/', $path);
        $i = count($path) - 1;
        if ($colorScheme != current(array_splice($path, $i - 1, 1, []))) {
            return null;
        }

        return implode('/', $path);
    }

    /**
     * @param $config
     *
     * @return mixed
     */
    protected function _getColorScheme($config)
    {
        $groups = $this->_dataObjectFactory->create([
            'data' => $this->_request->getParam('groups', [])
        ]);

        if ($colorScheme = $groups->getDataByPath('color_settings/fields/color_scheme/value')) {
            return $colorScheme;
        }

        return isset($config[ThemeOptionsHelper::XML_PATH_COLOR_SCHEME]) ?
            $config[ThemeOptionsHelper::XML_PATH_COLOR_SCHEME]
            : null;
    }
}