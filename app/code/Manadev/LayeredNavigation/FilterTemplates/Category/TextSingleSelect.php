<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation\FilterTemplates\Category;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Store\Model\StoreManagerInterface;
use Manadev\LayeredNavigation\Contracts\FilterTemplate;
use Manadev\LayeredNavigation\Models\Filter;
use Manadev\ProductCollection\Contracts\ProductCollection;
use Manadev\LayeredNavigation\RequestParser;
use Manadev\ProductCollection\Factory;

class TextSingleSelect extends FilterTemplate {
    /**
     * @var RequestParser
     */
    protected $requestParser;
    /**
     * @var Factory
     */
    protected $factory;
    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(RequestParser $requestParser, Factory $factory,
        CategoryRepositoryInterface $categoryRepository, StoreManagerInterface $storeManager)
    {
        $this->requestParser = $requestParser;
        $this->factory = $factory;
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Filter $filter
     * @return string
     */
    public function getFilename(Filter $filter) {
        return 'Manadev_LayeredNavigation::filter/single-select.phtml';
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
        $query = $productCollection->getQuery();
        $appliedCategory = false;

        if (($appliedCategoryId = $this->requestParser->readSingleValueInteger($name)) !== false) {
            $query->getFilterGroup('layered_nav')->addOperand($this->factory->createLayeredCategoryFilter(
                $name, [$appliedCategoryId]));

            /* @var $appliedCategory Category */
            $appliedCategory = $this->categoryRepository->get($appliedCategoryId,
                $this->storeManager->getStore()->getId());
        }


        $query->addFacet($this->factory->createChildCategoryFacet($name, $appliedCategory,
            $filter->getData('hide_filter_with_single_visible_item')));
    }

    /**
     * @param string $values
     *
     * @return mixed|bool
     */
    public function getAppliedOptions($values) {
        return $this->requestParser->readSingleValueIntegerString($values);
    }

    /**
     * @param ProductCollection $productCollection
     * @param Filter $filter
     * @return array
     */
    public function getAppliedItems(ProductCollection $productCollection, Filter $filter) {
        $name = $filter->getData('param_name');

        if (($appliedCategoryId = $this->requestParser->readSingleValueInteger($name)) !== false) {
            /* @var $appliedCategory Category */
            $appliedCategory = $this->categoryRepository->get($appliedCategoryId,
                $this->storeManager->getStore()->getId());

            yield [
                'label' => $appliedCategory->getName(),
                'value' => $appliedCategory->getId(),
            ];
        }
    }

    public function getTitle() {
        return __('Single Select Text');
    }

    public function getType() {
        return 'category';
    }
}