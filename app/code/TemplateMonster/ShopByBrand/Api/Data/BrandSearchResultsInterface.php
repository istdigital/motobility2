<?php
/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\ShopByBrand\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface BrandSearchResultsInterface
 *
 * @package TemplateMonster\ShopByBrand\Api\Data
 */
interface BrandSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get blocks list.
     *
     * @return \TemplateMonster\ShopByBrand\Api\Data\BrandInterface[]
     */
    public function getItems();

    /**
     * Set blocks list.
     *
     * @param \TemplateMonster\ShopByBrand\Api\Data\BrandInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}