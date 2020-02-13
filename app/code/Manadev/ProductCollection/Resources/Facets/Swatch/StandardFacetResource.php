<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\ProductCollection\Resources\Facets\Swatch;

use Manadev\ProductCollection\Configuration;
use Manadev\ProductCollection\Contracts\Facet;
use Manadev\ProductCollection\FacetSorter;
use Manadev\ProductCollection\Resources\Facets\Dropdown\StandardFacetResource as DropdownStandardFacetResource;
use Manadev\ProductCollection\Facets\Dropdown\StandardFacet;
use Manadev\ProductCollection\Resources\HelperResource;
use Magento\Store\Model\StoreManagerInterface;
use Manadev\ProductCollection\Factory;
use Magento\Framework\Model\ResourceModel\Db;
use Magento\Eav\Model\Config;

class StandardFacetResource extends DropdownStandardFacetResource
{
    /**
     * @var \Magento\Swatches\Helper\Data
     */
    protected $swatchHelper;

    public function __construct(Db\Context $context, Factory $factory,
        StoreManagerInterface $storeManager, Configuration $configuration,
        HelperResource $helperResource, Config $config, FacetSorter $sorter,
        \Magento\Swatches\Helper\Data $swatchHelper, $resourcePrefix = null)
    {
        parent::__construct($context, $factory, $storeManager, $configuration, $helperResource, $config,
            $sorter, $resourcePrefix);
        $this->swatchHelper = $swatchHelper;
    }

    public function populateOptions(Facet $facet, $counts) {
        /* @var $facet StandardFacet */
        $attribute = $this->config->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $facet->getAttributeId());
        $options = $attribute->getFrontend()->getSelectOptions();
        $selectedOptionIds = $facet->getSelectedOptionIds();
        $additionalData = $this->getAdditionalData($options);

        $optionIds = [];
        foreach ($options as $option) {
            $optionIds[] = $option['value'];
        }
        $swatches = $this->swatchHelper->getSwatchesByOptionsId($optionIds);

        $emptyOptionSortOrder = false;
        foreach ($options as $sortOrder => &$option) {
            if ($option['value'] === '' && $option['label'] === '') {
                $emptyOptionSortOrder = $sortOrder;
                continue;
            }

            if (isset($additionalData[$option['value']])) {
                $option = array_merge($option, $additionalData[$option['value']]);
            }

            $option['count'] = isset($counts[$option['value']]) ? $counts[$option['value']] : 0;
            $option['is_selected'] = $selectedOptionIds !== false ? in_array($option['value'], $selectedOptionIds) : false;
            $option['sort_order'] = $sortOrder;
            if (isset($swatches[$option['value']])) {
                $option['swatch_type'] = $swatches[$option['value']]['type'];
                $option['swatch'] = $swatches[$option['value']]['value'];
            }
            else {
                $option['swatch_type'] = '0';
                $option['swatch'] = $option['label'];
            }
        }

        if ($emptyOptionSortOrder !== false) {
            unset($options[$emptyOptionSortOrder]);
        }

        return $options;
    }
}