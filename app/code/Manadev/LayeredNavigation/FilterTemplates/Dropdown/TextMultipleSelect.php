<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation\FilterTemplates\Dropdown;

use Manadev\Core\CatalogAttributes;
use Manadev\LayeredNavigation\Contracts\FilterTemplate;
use Manadev\LayeredNavigation\Models\Filter;
use Manadev\LayeredNavigation\RequestParser;
use Manadev\ProductCollection\Enums\Operation;
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
     * @var \Magento\Swatches\Helper\Data
     */
    protected $swatchHelper;
    /**
     * @var CatalogAttributes
     */
    protected $catalogAttributes;

    public function __construct(RequestParser $requestParser, Factory $factory,
        \Magento\Swatches\Helper\Data $swatchHelper, CatalogAttributes $catalogAttributes) {
        $this->requestParser = $requestParser;
        $this->factory = $factory;
        $this->swatchHelper = $swatchHelper;
        $this->catalogAttributes = $catalogAttributes;
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
        return true;
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

        if (($appliedOptions = $this->requestParser->readMultipleValueInteger($name)) !== false) {
            $query->getFilterGroup('layered_nav')->addOperand($this->factory->createLayeredDropdownFilter(
                $name, $attributeId, $appliedOptions,
                $filter->getData('use_logical_and') ? Operation::LOGICAL_AND : Operation::LOGICAL_OR));
        }

        if ($filter->getData('minimum_product_count_per_option') > 0) {
            $query->addFacet($this->factory->createOptimizedDropdownFacet($name, $attributeId, $appliedOptions,
                $filter->getData('hide_filter_with_single_visible_item'),
                $filter->getData('show_selected_options_first'),
                $filter->getData('sort_options_by')));
        }
        else {
            $query->addFacet($this->factory->createStandardDropdownFacet($name, $attributeId, $appliedOptions,
                $filter->getData('hide_filter_with_single_visible_item'),
                $filter->getData('show_selected_options_first'),
                $filter->getData('sort_options_by')));
        }
    }

    /**
     * @param string $values
     *
     * @return mixed|bool
     */
    public function getAppliedOptions($values) {
        return $this->requestParser->readMultipleValueIntegerString($values);
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

        if ($facet->getData() !== false) {
            foreach ($facet->getData() as $item) {
                if ($item['is_selected']) {
                    yield $item;
                }
            }
        }

        if ($facet->getSelectedData() !== false) {
            foreach ($facet->getSelectedData() as $item) {
                if ($item['is_selected']) {
                    yield $item;
                }
            }
        }

    }

    public function getTitle() {
        return __('Multiple Select Text');
    }

    public function getType() {
        return 'dropdown';
    }
}