<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
namespace Manadev\ProductCollection;

use Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory;
use Magento\Catalog\Model\Layer\FilterableAttributeListInterface;
use Manadev\ProductCollection\Contracts\Facet;
use Manadev\ProductCollection\Contracts\ProductCollection;
use Manadev\ProductCollection\Registries\FacetResources;
use Symfony\Component\Config\Definition\Exception\Exception;

class FacetGenerator
{
    protected $collection;

    /**
     * @var FilterableAttributeListInterface
     */
    private $filterableAttributes;
    /**
     * @var Factory
     */
    private $factory;
    /**
     * @var FacetResources
     */
    private $facetResources;
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * FacetGenerator constructor.
     * @param \Magento\Catalog\Model\Layer\Category\FilterableAttributeList $filterableAttributes
     * @param Factory $factory
     * @param FacetResources $facetResources
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\Category\FilterableAttributeList $filterableAttributes,
        Factory $factory,
        FacetResources $facetResources,
        Configuration $configuration
    ) {
        $this->filterableAttributes = $filterableAttributes;
        $this->factory = $factory;
        $this->facetResources = $facetResources;
        $this->configuration = $configuration;
    }

    /**
     * @param $field
     * @return Facet
     */
    public function getFacet($field)
    {
        $facet = false;
        if($field == "category") {
            $facet = $this->factory->createChildCategoryFacet($field, [], false);
        } else {
            /** @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $attributes */
            $attributes = $this->filterableAttributes->getList();
            $attribute = $attributes->getItemByColumnValue('attribute_code', $field);
            switch($attribute->getFrontendInput()) {
                case "boolean":
                case "multiselect":
                case "select":
                    $facet = $this->factory->createOptimizedDropdownFacet($field, $attribute->getId(),
                        false, false, false, 'position');
                    break;
                case "price":
                    switch ($this->configuration->getPriceRangeCalculationMethod()) {
                        case AlgorithmFactory::RANGE_CALCULATION_IMPROVED:
                            $facet = $this->factory->createEqualizedCountPriceFacet($field, false,
                                false);
                            break;
                        case AlgorithmFactory::RANGE_CALCULATION_MANUAL:
                            $facet = $this->factory->createManualRangePriceFacet($field, false,
                                false);
                            break;
                        default:
                            $facet = $this->factory->createEqualizedRangePriceFacet($field, false,
                                false);
                            break;
                    }
                    break;
            }
        }

        return $facet;
    }

    public function getFacetedData($field) {
        $facet = $this->getFacet($field);
        if(!$facet) {
            throw new Exception("Unknown Facet for field `{$field}`");
        }

        $facetResource = $this->facetResources->get($facet->getType());
        $facet->setQuery($this->getCollection()->getQuery());
        $facetedData = $facetResource->count(clone $this->getCollection()->getSelect(), $facet);
        $result = [];
        if($facetedData) {
            foreach($facetedData as $data) {
                $value = str_replace("-", "_", $data['value']);
                $result[$value] = [
                    'value' => $value,
                    'count' => $data['count'],
                ];
            }
        }

        return $result;
    }

    public function setCollection($collection) {
        $this->collection = $collection;
    }

    /**
     * @return ProductCollection
     */
    public function getCollection() {
        return $this->collection;
    }
}