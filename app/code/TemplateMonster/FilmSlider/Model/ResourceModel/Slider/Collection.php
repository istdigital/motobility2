<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FilmSlider\Model\ResourceModel\Slider;

use TemplateMonster\FilmSlider\Model\ResourceModel\SliderAbstractCollection;

class Collection extends SliderAbstractCollection
{

    protected $_idFieldName = 'slider_id';

    protected function _construct()
    {
        $this->_init('TemplateMonster\FilmSlider\Model\Slider',
            'TemplateMonster\FilmSlider\Model\ResourceModel\Slider');
        $this->_map['fields']['slider_id'] = 'main_table.slider_id';
        $this->_map['fields']['store'] = 'store_table.store_id';
    }

    /**
     * Add filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
        }
        return $this;
    }

    /**
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $this->performAfterLoad('film_slider_store', 'slider_id');

        return parent::_afterLoad();
    }

    /**
     * Perform operations before rendering filters
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $this->joinStoreRelationTable('film_slider_store', 'slider_id');
    }
}
