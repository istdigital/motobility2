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
use TemplateMonster\FilmSlider\Api\Data\SliderInterface;

class Slider extends AbstractModel implements SliderInterface // IdentityInterface,
{

    const STATUS_ENABLED = 1;

    const STATUS_DISABLED = 0;

    /**
     * @var string
     */
    protected $_eventPrefix = 'film_slider';

    /**
     * @var string
     */
    protected $_eventObject = 'slider';

    protected $_idFieldName = 'slider_id';

    protected $_sliderItemCollection;

    public function __construct(\Magento\Framework\Model\Context $context,
                                \Magento\Framework\Registry $registry,
                                \TemplateMonster\FilmSlider\Model\ResourceModel\SliderItem\CollectionFactory $sliderItemCollection,
                                \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
                                \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
                                array $data = [])
    {
        $this->_sliderItemCollection = $sliderItemCollection;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function _construct()
    {
        $this->_init('TemplateMonster\FilmSlider\Model\ResourceModel\Slider');
    }

    /*/TODO:Cache type.
    public function getIdentities()
    {

    }*/

    public function getStatus()
    {
        return $this->_getData(self::STATUS);
    }

    public function getParams()
    {
        return $this->_getData(self::PARAMS);
    }

    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    public function setParams($params)
    {
        return $this->setData(self::PARAMS, $params);
    }

    public function getSlideCollection()
    {
        return $this->_sliderItemCollection->create()->_applyBySliderFilter($this);
    }

    /**
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    /**
     * Receive slider store ids
     *
     * @return int[]
     */
    public function getStores()
    {
        return $this->hasData('stores') ? $this->getData('stores') : $this->getData('store_id');
    }

    public function getActiveSlidesCount()
    {
        return $this->_getResource()->getActiveSlidesCount($this);
    }
}
