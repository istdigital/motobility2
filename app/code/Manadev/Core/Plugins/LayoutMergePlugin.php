<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core\Plugins;

use Magento\Framework\View\Model\Layout\Merge;
use Manadev\Core\Configuration;
use Magento\Framework\Filesystem\DriverPool;

class LayoutMergePlugin
{
    protected $methods = [];
    protected $properties = [];

    protected $layoutUpdatesCache;

    /**
     * @var Configuration
     */
    protected $configuration;

    public function __construct(Configuration $configuration) {
        $this->configuration = $configuration;
    }

    public function aroundGetFileLayoutUpdatesXml(Merge $merge, callable $proceed) {
        if (!$this->configuration->isLayoutXmlLoggingEnabled()) {
            return $proceed();
        }

        if ($this->layoutUpdatesCache) {
            return $this->layoutUpdatesCache;
        }

        $result = $this->_loadFileLayoutUpdatesXml($merge);
        $this->layoutUpdatesCache = $result;
        return $result;
    }

    protected function _loadFileLayoutUpdatesXml($merge) {
        $layoutStr = '';
        $theme = $this->_getPhysicalTheme($merge, $this->theme($merge));
        $updateFiles = $this->fileSource($merge)->getFiles($theme, '*.xml');
        $updateFiles = array_merge($updateFiles, $this->pageLayoutFileSource($merge)->getFiles($theme, '*.xml'));
        $useErrors = libxml_use_internal_errors(true);
        foreach ($updateFiles as $file) {
            /** @var $fileReader \Magento\Framework\Filesystem\File\Read   */
            $fileReader = $this->readFactory($merge)->create($file->getFilename(), DriverPool::FILE);
            $fileStr = $fileReader->readAll($file->getName());
            $fileStr = $this->_substitutePlaceholders($merge, $fileStr);
            /** @var $fileXml \Magento\Framework\View\Layout\Element */
            $fileXml = $this->_loadXmlString($merge, $fileStr);
            if (!$fileXml instanceof \Magento\Framework\View\Layout\Element) {
                $this->_logXmlErrors($merge, $file->getFilename(), libxml_get_errors());
                libxml_clear_errors();
                continue;
            }
            if (!$file->isBase() && $fileXml->xpath(Merge::XPATH_HANDLE_DECLARATION)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    new \Magento\Framework\Phrase(
                        'Theme layout update file \'%1\' must not declare page types.',
                        [$file->getFileName()]
                    )
                );
            }
            $handleName = basename($file->getFilename(), '.xml');
            $tagName = $fileXml->getName() === 'layout' ? 'layout' : 'handle';
            $handleAttributes = ' id="' . $handleName . '"' . $this->_renderXmlAttributes($merge, $fileXml);
            $handleStr = '<' . $tagName . $handleAttributes . '>' .
                '<referenceContainer name="debug">' .
                    '<referenceBlock name="debug">' .
                        '<action method="setFile">' .
                            '<argument name="value" xsi:type="string">' . $file->getFilename() . '</argument>' .
                        '</action>' .
                    '</referenceBlock>' .
                '</referenceContainer>' .
                $fileXml->innerXml() .
                '</' . $tagName . '>';
            $layoutStr .= $handleStr;
        }
        libxml_use_internal_errors($useErrors);
        $layoutStr = '<layouts xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' . $layoutStr . '</layouts>';
        $layoutXml = $this->_loadXmlString($merge, $layoutStr);
        return $layoutXml;
    }

    protected function _getPhysicalTheme($merge, $theme) {
        return $this->method('_getPhysicalTheme')->invokeArgs($merge, array_slice(func_get_args(), 1));
    }

    protected function theme($merge) {
        return $this->property('theme')->getValue($merge);
    }

    protected function fileSource($merge) {
        return $this->property('fileSource')->getValue($merge);
    }

    protected function pageLayoutFileSource($merge) {
        return $this->property('pageLayoutFileSource')->getValue($merge);
    }

    protected function readFactory($merge) {
        return $this->property('readFactory')->getValue($merge);
    }

    protected function _substitutePlaceholders($merge, $fileStr) {
        return $this->method('_substitutePlaceholders')->invokeArgs($merge, array_slice(func_get_args(), 1));
    }

    protected function _loadXmlString($merge, $fileStr) {
        return $this->method('_loadXmlString')->invokeArgs($merge, array_slice(func_get_args(), 1));
    }

    protected function _logXmlErrors($merge, $getFilename, $libxml_get_errors) {
        return $this->method('_logXmlErrors')->invokeArgs($merge, array_slice(func_get_args(), 1));
    }

    protected function _renderXmlAttributes($merge, $fileXml) {
        return $this->method('_renderXmlAttributes')->invokeArgs($merge, array_slice(func_get_args(), 1));
    }

    protected function method($name) {
        if (isset($this->methods[$name])) {
            return $this->methods[$name];
        }

        $this->methods[$name] = $method = new \ReflectionMethod(Merge::class, $name);
        $method->setAccessible(true);
        return $method;
    }

    protected function property($name) {
        if (isset($this->properties[$name])) {
            return $this->properties[$name];
        }

        $this->properties[$name] = $property = new \ReflectionProperty(Merge::class, $name);
        $property->setAccessible(true);
        return $property;
    }
}