<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation;

use Manadev\LayeredNavigation\Contracts\FilterTemplate;
use Manadev\LayeredNavigation\Contracts\FilterType;
use Manadev\LayeredNavigation\Models\Filter;
use Magento\Framework\App\RequestInterface;

class EngineFilter {
    /**
     * @var Filter
     */
    protected $filter;
    /**
     * @var FilterType
     */
    protected $filterType;
    /**
     * @var FilterTemplate
     */
    protected $filterTemplate;
    /**
     * @var Engine
     */
    protected $engine;
    /**
     * @var RequestInterface
     */
    protected $request;

    public function __construct(Engine $engine, Filter $filter, FilterType $filterType, FilterTemplate $filterTemplate,
        RequestInterface $request)
    {
        $this->filter = $filter;
        $this->filterType = $filterType;
        $this->filterTemplate = $filterTemplate;
        $this->engine = $engine;
        $this->request = $request;
    }

    /**
     * @return Filter
     */
    public function getFilter() {
        return $this->filter;
    }

    /**
     * @return FilterType
     */
    public function getFilterType() {
        return $this->filterType;
    }

    /**
     * @return FilterTemplate
     */
    public function getFilterTemplate() {
        return $this->filterTemplate;
    }

    public function prepare() {
        $this->filterTemplate->prepare($this->engine->getProductCollection(), $this->filter);
    }

    public function isVisible() {
        return $this->getData() !== false;
    }

    public function getName() {
        return $this->filterTemplate->getFilterName() ?: $this->filter->getData('title');
    }

    public function getData() {
        if (!($facet = $this->getFacet())) {
            return false;
        }

        return $facet->getData();
    }

    public function getTemplateFilename() {
        return $this->filterTemplate->getFilename($this->filter);
    }

    public function getAppliedOptions() {
        return $this->filterTemplate->getAppliedOptions($this->request->getParam(
            $this->filter->getData('param_name')));
    }

    public function isApplied() {
        return $this->getAppliedOptions() !== false;
    }

    public function getAppliedItems() {
        return $this->filterTemplate->getAppliedItems($this->engine->getProductCollection(), $this->filter);
    }

    public function getAppliedItemTemplateFilename() {
        return $this->filterTemplate->getAppliedItemFilename();
    }

    public function isLabelHtmlEscaped() {
        return $this->filterTemplate->isLabelHtmlEscaped();
    }

    /**
     * @return bool|\Manadev\ProductCollection\Contracts\Facet
     */
    public function getFacet() {
        $name = $this->filter->getData('param_name');
        $query = $this->engine->getProductCollection()->getQuery();

        return $query->getFacet($name);
    }
}