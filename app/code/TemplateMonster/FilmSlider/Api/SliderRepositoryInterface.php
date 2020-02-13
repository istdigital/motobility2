<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FilmSlider\Api;

interface SliderRepositoryInterface
{

    /**
     * Save slider.
     *
     * @param \TemplateMonster\FilmSlider\Api\Data\SliderInterface $slider
     * @return \TemplateMonster\FilmSlider\Api\Data\SliderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\TemplateMonster\FilmSlider\Api\Data\SliderInterface $slider);

    /**
     * Retrieve slider.
     *
     * @param int $sliderId
     * @return \TemplateMonster\FilmSlider\Api\Data\SliderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($sliderId);

    /**
     * Retrieve sliders matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \TemplateMonster\FilmSlider\Api\Data\SliderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete slider.
     *
     * @param \TemplateMonster\FilmSlider\Api\Data\SliderInterface $slider
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\TemplateMonster\FilmSlider\Api\Data\SliderInterface $slider);

    /**
     * Delete slider by ID.
     *
     * @param int $sliderId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($sliderId);

    /**
     * @return mixed
     */
    public function getModelInstance();
}
