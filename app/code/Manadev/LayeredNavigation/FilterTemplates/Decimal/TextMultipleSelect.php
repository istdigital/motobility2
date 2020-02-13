<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation\FilterTemplates\Decimal;

use Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory;
use Magento\Catalog\Model\ResourceModel\Product;
use Manadev\Core\Exceptions\NotImplemented;
use Manadev\LayeredNavigation\Configuration;
use Manadev\LayeredNavigation\Contracts\FilterTemplate;
use Manadev\LayeredNavigation\Models\Filter;
use Manadev\LayeredNavigation\RequestParser;
use Manadev\ProductCollection\Factory;
use Manadev\ProductCollection\Contracts\ProductCollection;

class TextMultipleSelect extends FilterTemplate {
    /**
     * @var RequestParser
     */
    protected $requestParser;
    /**
     * @var Factory
     */
    protected $factory;
    /**
     * @var Configuration
     */
    protected $configuration;

    public function __construct(RequestParser $requestParser, Factory $factory, Configuration $configuration) {
        $this->requestParser = $requestParser;
        $this->factory = $factory;
        $this->configuration = $configuration;
    }

    /**
     * @param Filter $filter
     * @return string
     */
    public function getFilename(Filter $filter) {
        return 'Manadev_LayeredNavigation::filter/multiple-select.phtml';
    }

    /**
     * @return string
     */
    public function getAppliedItemFilename() {
        return 'Manadev_LayeredNavigation::applied-item/standard.phtml';
    }

    public function isLabelHtmlEscaped() {
        return false;
    }

    /**
     * Registers filtering and counting logic with product collection
     *
     * @param ProductCollection $productCollection
     * @param Filter $filter
     */
    public function prepare(ProductCollection $productCollection, Filter $filter) {
        $name = $filter->getData('param_name');
        $attributeId = $filter->getData('attribute_id');
        $query = $productCollection->getQuery();

        // TODO
        if (($appliedRanges = $this->requestParser->readMultipleValueRange($name)) !== false) {
            $query->getFilterGroup('layered_nav')->addOperand($this->factory->createLayeredDecimalFilter(
                $name, $attributeId, $appliedRanges));
        }

        $query->addFacet($this->factory->createEqualizedRangeDecimalFacet($name, $attributeId, $appliedRanges,
            $filter->getData('hide_filter_with_single_visible_item')));
    }

    /**
     * @param string $values
     *
     * @return mixed|bool
     */
    public function getAppliedOptions($values) {
        return $this->implodeRanges($this->requestParser->readMultipleValueRangeString($values));
    }

    /**
     * @param ProductCollection $productCollection
     * @param Filter $filter
     * @return array
     */
    public function getAppliedItems(ProductCollection $productCollection, Filter $filter) {
        $name = $filter->getData('param_name');
        $query = $productCollection->getQuery();

        if (!($facet = $query->getFacet($name))) {
            return;
        }

        if ($facet->getData() === false) {
            return;
        }

        foreach ($facet->getData() as $item) {
            if ($item['is_selected']) {
                yield $item;
            }
        }
    }

    protected function implodeRanges($ranges) {
        if (!$ranges) {
            return false;
        }

        return array_map(function($range) { return implode('-', $range); }, $ranges);
    }

    public function getTitle() {
        return __('Multiple Select Text');
    }

    public function getType() {
        return 'decimal';
    }
}