<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\ProductCollection;

use Closure;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Store\Model\StoreManagerInterface;
use Manadev\ProductCollection\Contracts\Facet;
use Manadev\ProductCollection\Contracts\Filter;
use Manadev\ProductCollection\Filters\LogicalFilter;
use Manadev\ProductCollection\Contracts\ProductCollection;

class Query
{
    /**
     * @var LogicalFilter
     */
    protected $filters;
    /**
     * @var Factory
     */
    protected $factory;

    /**
     * @var Facet[]
     */
    protected $facets = [];

    /**
     * @var Category
     */
    protected $category;

    /**
     * @var ProductCollection
     */
    protected $productCollection;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    public function __construct(Factory $factory, StoreManagerInterface $storeManager,
        CategoryRepositoryInterface $categoryRepository)
    {
        $this->factory = $factory;
        $this->filters = $this->factory->createLogicalFilter('root');
        $this->storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
    }

    public function eachFilter(Closure $callback) {
        $this->eachFilterRecursively($this->filters, $callback);
    }

    protected function eachFilterRecursively(Filter $filter, Closure $callback) {
        if (!$callback($filter)) {
            return false;
        }

        if ($filter instanceof LogicalFilter) {
            foreach ($filter->getOperands() as $operand) {
                if (!$this->eachFilterRecursively($operand, $callback)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param $name
     * @param null $factoryMethodCallback
     * @return bool|Filter|LogicalFilter
     * @throws \Manadev\Core\Exceptions\InvalidState
     */
    public function getFilterGroup($name, $factoryMethodCallback = null) {
        if ($filterGroup = $this->filters->getOperand($name)) {
            return $filterGroup;
        }

        if ($factoryMethodCallback) {
            $filterGroup = call_user_func($factoryMethodCallback, $name);
        }
        else {
            $filterGroup = $this->factory->createLogicalFilter($name);
        }

        $this->filters->addOperand($filterGroup);

        return $filterGroup;
    }

    public function getFilters() {
        return $this->filters;
    }

    public function addFacet(Facet $facet) {
        $this->facets[$facet->getName()] = $facet;
        $facet->setQuery($this);
    }

    public function getFacets() {
        return $this->facets;
    }

    /**
     * @param $name
     * @return bool|Facet
     */
    public function getFacet($name) {
        return isset($this->facets[$name]) ? $this->facets[$name] : false;
    }

    /**
     * @return Category
     */
    public function getCategory() {
        if (!$this->category) {
            $this->category = $this->categoryRepository->get($this->storeManager->getStore()->getRootCategoryId());
        }
        return $this->category;
    }

    /**
     * @param mixed $category
     */
    public function setCategory($category) {
        $this->category = $category;
    }

    /**
     * @return ProductCollection
     */
    public function getProductCollection() {
        return $this->productCollection;
    }

    /**
     * @param ProductCollection $productCollection
     */
    public function setProductCollection($productCollection) {
        $this->productCollection = $productCollection;
    }
}