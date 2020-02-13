<?php

namespace TemplateMonster\ThemeOptions\Helper;

use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory as DataCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Config\Reader\Filesystem;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Color Scheme Helper.
 *
 * @package TemplateMonster\ThemeOptions\Helper
 */
class ColorScheme extends AbstractHelper
{
    /**
     * @var DataCollectionFactory
     */
    protected $_dataCollectionFactory;

    /**
     * @var DataObjectFactory
     */
    protected $_dataObjectFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var JsonHelper
     */
    protected $_jsonHelper;

    /**
     * @var Filesystem
     */
    protected $_reader;

    /**
     * @var \Magento\Framework\DataObject|null
     */
    private $_schemes = null;

    /**
     * ColorScheme constructor.
     *
     * @param DataCollectionFactory $dataCollectionFactory
     * @param DataObjectFactory     $dataObjectFactory
     * @param StoreManagerInterface $storeManager
     * @param JsonHelper            $jsonHelper
     * @param Filesystem            $reader
     * @param Context               $context
     */
    public function __construct(
        DataCollectionFactory $dataCollectionFactory,
        DataObjectFactory $dataObjectFactory,
        StoreManagerInterface $storeManager,
        JsonHelper $jsonHelper,
        Filesystem $reader,
        Context $context
    )
    {
        $this->_dataCollectionFactory = $dataCollectionFactory;
        $this->_dataObjectFactory = $dataObjectFactory;
        $this->_storeManager = $storeManager;
        $this->_jsonHelper = $jsonHelper;
        $this->_reader = $reader;
        parent::__construct($context);
    }

    public function getColorSchemes()
    {
        $this->_initColorSchemes();

        return $this->_schemes->toArray();
    }

    public function getStoreColorSchemes($store = null)
    {
        $this->_initColorSchemes();

        return $this->_schemes->getDataByKey($this->_getWebsiteCode($store));

    }
    /**
     * Get default values.
     *
     * @param string|null $store
     *
     * @return array
     */
    public function getDefaultStoreValues($store = null)
    {
        $values = [];
        $values_data = $this->getStoreColorSchemes($store);
        if(count($values_data)) {
            foreach ($values_data as $id => $data) {
                $values[$id] = $data['params'];
            }
        }

        return $values;
    }

    /**
     * Get default value.
     *
     * @param string      $scheme
     * @param string      $path
     * @param string|null $store
     *
     * @return null
     */
    public function getDefaultValue($scheme, $path, $store = null)
    {
        $this->_initColorSchemes();

        $path = sprintf(
            '%s/%s/params/%s',
            $this->_getWebsiteCode($store),
            $scheme,
            $this->_getParamNameByPath($path)
        );

        return $this->_schemes->getDataByPath($path);
    }

    /**
     * Get default values in JSON.
     *
     * @param string|null $store
     *
     * @return string
     */
    public function getJsonDefaultValues($store = null)
    {
        $params = $this->getDefaultStoreValues($store);

        return $this->_jsonHelper->jsonEncode($params);
    }

    /**
     * Get user defined values.
     *
     * @param string|null $store
     *
     * @return array
     */
    public function getUserValues($store = null)
    {
        if (null === $store) {
            $store = $this->_storeManager->getStore()->getId();
        }

        $values = [];
        /** @var \Magento\Framework\App\Config\Value $value */
        foreach ($this->_getDataCollection($store) as $value) {
            $path = $value->getPath();
            $scheme = $this->_getColorSchemeByPath($path);
            $name = $this->_getParamNameByPath($path);
            if (!isset($value[$scheme])) {
                $value[$scheme] = [];
            }
            $values[$scheme][$name] = $value->getValue();
        }

        return $values;
    }

    /**
     * Get user defined values in JSON.
     *
     * @param string|null $store
     *
     * @return string
     */
    public function getJsonUserValues($store = null)
    {
        $values = $this->getUserValues($store);

        return $this->_jsonHelper->jsonEncode($values);
    }

    /**
     * @return \Magento\Framework\DataObject|null
     */
    protected function _initColorSchemes()
    {
        if (null === $this->_schemes) {
            $values = [];
            foreach ($this->_reader->read() as $website => $scheme) {
                foreach ($scheme as $id => $data) {
                    $values[$website][$id] = $data;
                }
            }

            $this->_schemes = $this->_dataObjectFactory->create(['data' => $values]);
        }

        return $this->_schemes;
    }

    /**
     * Get color scheme by path.
     *
     * @param string $path
     *
     * @return string
     */
    protected function _getColorSchemeByPath($path)
    {
        $path = explode('/', $path);

        return current(array_splice($path, count($path) - 2, 1, []));
    }

    /**
     * Get param name by path.
     *
     * @param string $path
     *
     * @return string
     */
    protected function _getParamNameByPath($path)
    {
        $path = explode('/', $path);

        return end($path);
    }

    /**
     * Get data collection filtered by color settings group.
     *
     * @param string $store
     *
     * @return \Magento\Config\Model\ResourceModel\Config\Data\Collection
     */
    protected function _getDataCollection($store)
    {
        /** @var \Magento\Config\Model\ResourceModel\Config\Data\Collection $collection */
        $collection = $this->_dataCollectionFactory->create();
        $collection->addScopeFilter('stores', $store, Data::XML_PATH_COLOR_SETTING_GROUP);

        return $collection;
    }

    /**
     * Get website code.
     *
     * @param string $store
     *
     * @return string
     */
    protected function _getWebsiteCode($store = null)
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->_storeManager->getStore($store);

        return $store->getWebsite()->getCode();
    }
}