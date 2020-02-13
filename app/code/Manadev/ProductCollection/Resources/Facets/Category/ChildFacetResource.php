<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\ProductCollection\Resources\Facets\Category;

use Magento\Catalog\Model\Category;
use Magento\Framework\DB\Select;
use Magento\Store\Model\StoreManagerInterface;
use Manadev\Core\Exceptions\NotImplemented;
use Manadev\ProductCollection\Configuration;
use Manadev\ProductCollection\Contracts\Facet;
use Manadev\ProductCollection\Contracts\FacetResource;
use Manadev\ProductCollection\Facets\Category\ChildFacet;
use Manadev\ProductCollection\Factory;
use Manadev\ProductCollection\Resources\HelperResource;
use Magento\Framework\Model\ResourceModel\Db;

class ChildFacetResource extends FacetResource
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Flat
     */
    protected $flatResource;

    public function __construct(Db\Context $context, Factory $factory,
        StoreManagerInterface $storeManager, Configuration $configuration,
        \Magento\Catalog\Model\ResourceModel\Category\Flat $flatResource, HelperResource $helperResource,
        $resourcePrefix = null)
    {
        parent::__construct($context, $factory, $storeManager, $configuration, $helperResource, $resourcePrefix);
        $this->flatResource = $flatResource;
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct() {
        $this->_setMainTable('catalog_product_entity');
    }

    /**
     * @param Select $select
     * @param Facet $facet
     * @return mixed
     */
    public function count(Select $select, Facet $facet) {
        /* @var $facet ChildFacet */

        $category = $facet->getAppliedCategory() ?: $facet->getQuery()->getCategory();
        if (!$category->getIsActive()) {
            return false;
        }

        $childCategories = $category->getChildrenCategories();
        $facet->getQuery()->getProductCollection()->addCountToCategoriesOnSelect($select, $childCategories);

        $result = [];
        foreach ($childCategories as $childCategory) {
            /* @var $childCategory Category */
            if (!$childCategory->getIsActive()) {
                continue;
            }

            if (!$childCategory->getData('product_count')) {
                continue;
            }

            $result[] = [
                'label' => $childCategory->getName(),
                'value' => $childCategory->getId(),
                'count' => $childCategory->getData('product_count'),
                'is_selected' => 0,
                'sort_order' => count($result),
            ];
        }

        $minimumOptionCount = $facet->getHideWithSingleVisibleItem() ? 2 : 1;
        return count($result) >= $minimumOptionCount ? $result : false;
    }

    public function getFilterCallback(Facet $facet) {
        return null;
    }
}