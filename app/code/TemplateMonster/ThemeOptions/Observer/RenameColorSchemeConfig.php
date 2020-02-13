<?php

namespace TemplateMonster\ThemeOptions\Observer;

use TemplateMonster\ThemeOptions\Helper\Data as ThemeOptionsHelper;
use Magento\Config\Model\ResourceModel\Config\Data\Collection as ConfigDataCollection;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class RenameColorSchemeConfig
 *
 * @package TemplateMonster\ThemeOptions\Observer
 */
class RenameColorSchemeConfig implements ObserverInterface
{
    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Framework\App\Config\Value $value */
        $value = $observer->getData('config_data');
        if ($this->_isColorSettingsValue($value)) {
            $path = $this->_getNewPath($value);
            $value->setPath($path);
        }
    }

    /**
     * @param Value $value
     *
     * @return bool
     */
    protected function _isColorSettingsValue(Value $value)
    {
        if ($value->getPath() === ThemeOptionsHelper::XML_PATH_COLOR_SCHEME) {
            return false;
        }

        return strpos($value->getPath(), ThemeOptionsHelper::XML_PATH_COLOR_SETTING_GROUP) === 0;
    }

    /**
     * Get new path.
     *
     * @param Value $value
     *
     * @return string
     */
    protected function _getNewPath(Value $value)
    {
        $path = explode('/', $value->getPath());
        $i = count($path) - 1;
        $shema = $this->_getColorScheme($value);
        if($shema) {
            array_splice($path, $i, 0, [$this->_getColorScheme($value)]);
        }
        return implode('/', $path);
    }

    /**
     * Get color scheme.
     *
     * @param Value $value
     *
     * @return mixed
     */
    protected function _getColorScheme(Value $value)
    {
        return $value->getDataByPath('fieldset_data/color_scheme');
    }
}