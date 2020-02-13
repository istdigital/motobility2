<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FilmSlider\Api;

interface SliderItemRepositoryInterface
{

    /**
     * Save sliderItem.
     *
     * @param \TemplateMonster\FilmSlider\Api\Data\SliderItemInterface $sliderItem
     * @return \TemplateMonster\FilmSlider\Api\Data\SliderItemInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\TemplateMonster\FilmSlider\Api\Data\SliderItemInterface $slider);

    /**
     * Retrieve sliderItem.
     *
     * @param int $sliderItemId
     * @return \TemplateMonster\FilmSlider\Api\Data\SliderItemInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($sliderItemId);

    /**
     * Retrieve sliderItems matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \TemplateMonster\FilmSlider\Api\Data\SliderItemInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete sliderItem.
     *
     * @param \TemplateMonster\FilmSlider\Api\Data\SliderItemInterface $sliderItem
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\TemplateMonster\FilmSlider\Api\Data\SliderItemInterface $sliderItem);

    /**
     * Delete sliderItem by ID.
     *
     * @param int $sliderItemId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($sliderItemId);

    /**
     * @return mixed
     */
    public function getModelInstance();
}
