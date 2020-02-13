<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FilmSlider\Model;

use Magento\Framework\Model\AbstractModel;
//use Magento\Framework\DataObject\IdentityInterface;
use TemplateMonster\FilmSlider\Api\Data\SliderItemInterface;

class SliderItem extends AbstractModel implements SliderItemInterface //IdentityInterface,
{

    const STATUS_ENABLED = 1;

    const STATUS_DISABLED = 0;

    protected $_eventPrefix = 'film_slider_item';

    protected $_eventObject = 'sliderItem';

    public function _construct()
    {
        $this->_init('TemplateMonster\FilmSlider\Model\ResourceModel\SliderItem');
    }

    //TODO:Cache type.
    /*public function getIdentities()
    {

    }*/

    public function getId()
    {
        return $this->_getData(self::SLIDE_ID);
    }


    public function getSlideritemId()
    {
        return $this->_getData(self::SLIDE_ID);
    }

    public function getParentId()
    {
        return $this->_getData(self::PARENT_ID);
    }

    public function getTitle()
    {
        return $this->_getData(self::TITLE);
    }

    public function getSortItem()
    {
        return $this->_getData(self::SORT_ITEM);
    }

    public function getStatus()
    {
        return $this->_getData(self::STATUS);
    }

    public function getImage()
    {
        return $this->_getData(self::IMAGE);
    }

    public function getImageParams()
    {
        return $this->_getData(self::IMAGE_PARAMS);
    }

    public function getLayerGeneralParams()
    {
        return $this->_getData(self::LAYER_GENERAL_PARAMS);
    }

    public function getLayerGeneralParamsArray()
    {
        return $this->_getData(self::LAYER_GENERAL_PARAMS_ARRAY);
    }

    public function getLayerAnimationParams()
    {
        return $this->_getData(self::LAYER_ANIMATION_PARAMS);
    }

    public function getImageParamsArray()
    {
        return $this->_getData(self::IMAGE_PARAMS_ARRAY);
    }

    public function setId($Id)
    {
        return $this->setData(self::SLIDE_ID, $Id);
    }


    public function setSlideritemId($slideritemId)
    {
        return $this->setData(self::SLIDE_ID, $slideritemId);
    }

    public function setParentId($parentId)
    {
        return $this->setData(self::PARENT_ID, $parentId);
    }

    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    public function setSortItem($sortItem)
    {
        return $this->setData(self::SORT_ITEM, $sortItem);
    }

    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    public function setImage($image)
    {
        return $this->setData(self::IMAGE, $image);
    }

    public function setImageParams($imageParams)
    {
        return $this->setData(self::IMAGE_PARAMS, $imageParams);
    }

    public function setLayerGeneralParams($layerGeneralParams)
    {
        return $this->setData(self::LAYER_GENERAL_PARAMS, $layerGeneralParams);
    }

    public function setLayerAnimationParams($layerAnimationParams)
    {
        return $this->setData(self::LAYER_ANIMATION_PARAMS, $layerAnimationParams);
    }

    /**
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    public function getLayerItems()
    {
        $result = [];
        if (!$this->getLayerGeneralParams()) {
            return $result;
        }

        $itemsArray = \ZEND_JSON::decode($this->getLayerGeneralParams());
        if (!$itemsArray) {
            return $result;
        }

        foreach ($itemsArray as $item) {
            $result[] = new \Magento\Framework\DataObject($item);
        }

        return $result;
    }
}
