<?php

namespace TemplateMonster\ShopByBrand\Api;

use TemplateMonster\ShopByBrand\Api\Data\BrandInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface BrandRepositoryInterface
 *
 * @package TemplateMonster\ShopByBrand\Api
 */
interface BrandRepositoryInterface
{
    /**
     * Save brand.
     *
     * @param \TemplateMonster\ShopByBrand\Api\Data\BrandInterface $brand
     * @return \TemplateMonster\ShopByBrand\Api\Data\BrandInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(BrandInterface $brand);

    /**
     * Retrieve brand.
     *
     * @param int $brandId
     * @return \TemplateMonster\ShopByBrand\Api\Data\BrandInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($brandId);

    /**
     * Retrieve brands matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \TemplateMonster\ShopByBrand\Api\Data\BrandSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete brand.
     *
     * @param \TemplateMonster\ShopByBrand\Api\Data\BrandInterface $brand
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(BrandInterface $brand);

    /**
     * Delete brand by ID.
     *
     * @param int $brandId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($brandId);
}