<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */


namespace Manadev\LayeredNavigation\Contracts;

use Magento\Catalog\Model\Layer;
use Manadev\LayeredNavigation\Models\Filter;
use Manadev\ProductCollection\Contracts\ProductCollection;

abstract class FilterTemplate {
    protected $filterName = '';

    /**
     * @param Filter $filter
     * @return string
     */
    abstract public function getFilename(Filter $filter);

    /**
     * @return string
     */
    abstract public function getAppliedItemFilename();

    /**
     * Registers filtering and counting logic with product collection
     *
     * @param ProductCollection $productCollection
     * @param Filter $filter
     */
    abstract public function prepare(ProductCollection $productCollection, Filter $filter);

    /**
     * @param string $values
     *
     * @return mixed|bool
     */
    abstract public function getAppliedOptions($values);

    /**
     * @param ProductCollection $productCollection
     * @param Filter $filter
     * @return array
     */
    abstract public function getAppliedItems(ProductCollection $productCollection, Filter $filter);

    abstract public function isLabelHtmlEscaped();

    abstract public function getTitle();

    abstract public function getType();

    public function isSlider() {
        return false;
    }

    public function isDropdown() {
        return false;
    }

    public function isCategory() {
        return $this->getType() == 'category';
    }

    public function isSwatch() {
        return $this->getType() == 'swatch';
    }

    public function isShowMoreApplicable() {
        return !$this->isSlider() && !$this->isCategory() && !$this->isSwatch() && !$this->isDropdown();
    }

    public function getFilterName() {
        return $this->filterName;
    }
}