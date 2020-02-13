<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\ShopByBrand\Model\Brand;

use TemplateMonster\ShopByBrand\Model\ResourceModel\Brand\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{

    /**
     * @var \Magento\Cms\Model\ResourceModel\Page\Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;


    protected $_imageTypePath = [
        'logo'=>'logo/logo/',
        'brand'=>'brandpage/brandpage/',
        'product'=>'brandproductpage/brandproductpage/'
    ];


    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $brandCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $brandCollectionFactory,
        DataPersistorInterface $dataPersistor,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $brandCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->_storeManager = $storeManager;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->_prepareImage($this->loadedData);
        }
        $items = $this->collection->getItems();

        foreach ($items as $brand) {
            $this->loadedData[$brand->getId()] = $brand->getData();
        }

        $data = $this->dataPersistor->get('brand');
        if (!empty($data)) {
            $brand = $this->collection->getNewEmptyItem();
            $brand->setData($data);
            $this->loadedData[$brand->getId()] = $brand->getData();
            $this->dataPersistor->clear('brand');
        }

        return $this->_prepareImage($this->loadedData);
    }


    protected function _prepareImage($data)
    {

        if(is_null($data)) {
            return [];
        }

        foreach($data as $key=>$preparedDataWithImg)
        {
            if(isset($preparedDataWithImg['brand_banner'])) {
                $value = $preparedDataWithImg['brand_banner'];
                $preparedDataWithImg['brand_banner'] = [];
                $preparedDataWithImg['brand_banner'][0]['url']  = $this->getImageUrl($value,'brand');
                $preparedDataWithImg['brand_banner'][0]['name']  = $value;
            }
            if(isset($preparedDataWithImg['product_banner'])) {
                $value = $preparedDataWithImg['product_banner'];
                $preparedDataWithImg['product_banner'] = [];
                $preparedDataWithImg['product_banner'][0]['url']  = $this->getImageUrl($value,'product');
                $preparedDataWithImg['product_banner'][0]['name']  = $value;
            }
            if(isset($preparedDataWithImg['logo'])) {
                $value = $preparedDataWithImg['logo'];
                $preparedDataWithImg['logo'] = [];
                $preparedDataWithImg['logo'][0]['url']  = $this->getImageUrl($value,'logo');
                $preparedDataWithImg['logo'][0]['name']  = $value;
            }
            $data[$key] = $preparedDataWithImg;
        }

        return $data;
    }

    /**
     * Retrieve image URL
     *
     * @return string
     */
    public function getImageUrl($image,$type)
    {
        $url = false;
        $path = $this->_imageTypePath[$type];
        if ($image) {
            if (is_string($image)) {
                $url = $this->_storeManager->getStore()->getBaseUrl(
                        \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                    ) . $path . $image;
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Something went wrong while getting the image url.')
                );
            }
        }
        return $url;
    }

}