<?php
/** 
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation;

use Magento\Catalog\Model\Layer;
use Manadev\LayeredNavigation\Registries\FilterTypes;
use Manadev\ProductCollection\Contracts\ProductCollection;

class Engine {

    /**
     * @var EngineFilter[]
     */
    protected $filters = [];

    /**
     * @var EngineFilter[]
     */
    protected $orderedFilters;

    /**
     * @var int[]
     */
    protected $filterPositions = [];

    /**
     * @var Factory
     */
    protected $factory;
    /**
     * @var Helper
     */
    protected $helper;
    /**
     * @var FilterTypes
     */
    protected $filterTypes;
    /**
     * @var Layer\Resolver
     */
    protected $layerResolver;
    /**
     * @var \ReflectionProperty
     */
    protected $layerProperty;

    public function __construct(Factory $factory, Layer\Resolver $layerResolver, Helper $helper,
        FilterTypes $filterTypes)
    {
        $this->factory = $factory;
        $this->helper = $helper;
        $this->filterTypes = $filterTypes;
        $this->layerResolver = $layerResolver;

        $this->layerProperty = new \ReflectionProperty(get_class($layerResolver), 'layer');
        $this->layerProperty->setAccessible(true);
    }

    public function getLayer() {
        return $this->layerProperty->getValue($this->layerResolver);
    }

    /**
     * @return ProductCollection
     */
    public function getProductCollection() {
        return $this->getLayer()->getProductCollection();
    }

    public function setCurrentCategory($id) {
        $this->resetFilters();
        $this->getLayer()->setCurrentCategory($id);
    }

    protected function resetFilters() {
        $this->filters = [];
    }

    public function prepareFiltersToShowIn($position, $defaultVisibility, $visibility) {
        foreach ($this->helper->getAllFilters() as $filter) {
            if (isset($this->filters[$filter->getData('filter_id')])) {
                continue;
            }

            if (!$filter->getData($position)) {
                continue;
            }

            if (isset($visibility[$filter->getData('param_name')])) {
                if ($visibility[$filter->getData('param_name')] === false) {
                    continue;
                }
            }
            elseif (!$defaultVisibility) {
                continue;
            }

            if (!($filterType = $this->filterTypes->get($filter->getData('type')))) {
                continue;
            }

            if (!($template = $filterType->getTemplates()->get($filter->getData('template')))) {
                continue;
            }

            // create an aggregate structure containing all relevant information about the filter
            $engineFilter = $this->factory->createEngineFilter($this, $filter, $filterType, $template);

            // register filter with the engine
            $this->filters[$filter->getData('filter_id')] = $engineFilter;

            // let the filter register its filtering and counting logic with product collection of the current page
            $engineFilter->prepare();
        }

        // the following fix forces product list to show on static block only categories when filter is applied
        if ($state = $this->getLayer()->getState()) {
            if (!$state->getFilters()) {
                foreach ($this->getAppliedFilters() as $filter) {
                    $state->addFilter($this->factory->createMagentoItem());
                    break;
                }
            }

        }
    }

    /**
     * @param $position
     * @return EngineFilter[]
     */
    public function getFiltersToShowIn($position) {
        foreach ($this->getOrderedFilters() as $engineFilter) {
            if (!$engineFilter->getFilter()->getData($position)) {
                continue;
            }

            yield $engineFilter;
        }
    }

    /**
     * @return EngineFilter[]|\Generator
     */
    public function getAppliedFilters() {
        foreach ($this->filters as $engineFilter) {
            if ($engineFilter->getAppliedOptions() === false) {
                continue;
            }

            yield $engineFilter;
        }
    }

    /**
     * @return EngineFilter[]
     */
    public function getFilters() {
        return $this->filters;
    }

    public function setFilterPosition($filter, $position) {
        $this->filterPositions[$filter] = $position;
    }

    public function getOrderedFilters() {
        if (!$this->orderedFilters) {
            $this->orderedFilters = $this->filters;
            uasort($this->orderedFilters, function($a, $b) {
                /* @var EngineFilter $a */
                /* @var EngineFilter $b */

                $aPosition = intval(isset($this->filterPositions[$a->getFilter()->getData('param_name')])
                    ? $this->filterPositions[$a->getFilter()->getData('param_name')]
                    : $a->getFilter()->getData('position'));
                $bPosition = intval(isset($this->filterPositions[$b->getFilter()->getData('param_name')])
                    ? $this->filterPositions[$b->getFilter()->getData('param_name')]
                    : $b->getFilter()->getData('position'));

                if ($aPosition < $bPosition) return -1;
                if ($aPosition > $bPosition) return 1;
                return 0;
            });
        }

        return $this->orderedFilters;
    }
}