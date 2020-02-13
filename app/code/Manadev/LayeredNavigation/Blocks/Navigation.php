<?php
/** 
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation\Blocks;

use Magento\Framework\View\Element\Template;
use Manadev\Core\Helpers\LayoutHelper;
use Manadev\LayeredNavigation\Configuration;
use Manadev\LayeredNavigation\Engine;
use Manadev\LayeredNavigation\EngineFilter;
use Manadev\LayeredNavigation\UrlGenerator;

class Navigation extends Template {
    /**
     * @var Engine
     */
    protected $engine;
    /**
     * @var UrlGenerator
     */
    protected $urlGenerator;
    /**
     * @var Configuration
     */
    protected $config;

    protected $_scripts = [];

    /**
     * By default all filters are visible
     *
     * @var bool
     */
    protected $defaultVisibility = true;

    /**
     * If filter is not listed in this array it is visible as specified in $defaultVisibility
     * property.
     *
     * @var bool[]
     */
    protected $visibility = [];

    /**
     * By default all applied filters are visible
     *
     * @var bool
     */
    protected $defaultAppliedFilterVisibility = true;

    /**
     * If applied filter is not listed in this array it is visible as specified in $defaultAppliedFilterVisibility
     * property.
     *
     * @var bool[]
     */
    protected $appliedFilterVisibility = [];


    /**
     * @var LayoutHelper
     */
    protected $layoutHelper;

    public function __construct(Template\Context $context, Engine $engine, UrlGenerator $urlGenerator,
        Configuration $config, LayoutHelper $layoutHelper,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->engine = $engine;
        $this->urlGenerator = $urlGenerator;
        $this->config = $config;
        $this->layoutHelper = $layoutHelper;
    }

    public function renderScripts() {
        return json_encode($this->_scripts, JSON_PRETTY_PRINT);
    }

    public function getApplyHtml() {
        /* @var \Manadev\LayeredNavigationAjax\Blocks\Apply $block */
        if (!($block = $this->getLayout()->getBlock('mana.layered-nav.apply'))) {
            return '';
        }

        return $block->getHtml();
    }

    protected function _prepareLayout() {
        $this->layoutHelper->afterLayoutIsLoaded(function() {
            $this->engine->prepareFiltersToShowIn($this->getData('position'), $this->defaultVisibility,
                $this->visibility);
        });

        return $this;
    }

    public function addScript($scriptName, $config = array(), $target = '*') {
        if(!isset($this->_scripts[$target])) {
            $this->_scripts[$target] = [];
        }

        $this->_scripts[$target][$scriptName] = $config;

        return $this;
    }

    public function getScripts() {
        return $this->_scripts;
    }

    public function setCategoryId($category_id) {
        $this->engine->setCurrentCategory($category_id);
    }

    public function isVisible() {
        $this->engine->getProductCollection()->loadFacets();
        foreach ($this->engine->getFiltersToShowIn($this->getData('position')) as $engineFilter) {
            if ($engineFilter->isVisible() && (
                    $this->isFilterVisible($engineFilter) ||
                    $this->isAppliedFilterVisibleAsSpecifiedInLayoutXml($engineFilter)))
            {
                return true;
            }
        }

        return false;
    }

    public function hasState() {
        foreach ($this->getAppliedFilters() as $engineFilter) {
            return true;
        }

        return false;
    }

    public function getClearUrl() {
        $filtersNotToBeCleared = [];
        foreach ($this->engine->getFilters() as $engineFilter) {
            if ($engineFilter->isApplied() && !$this->isAppliedFilterVisibleAsSpecifiedInLayoutXml($engineFilter)) {
                $filtersNotToBeCleared[] = $engineFilter;
            }
        }
        return $this->escapeUrl($this->urlGenerator->getClearAllUrl($filtersNotToBeCleared));
    }

    public function getClearLinkAttributes() {
        return $this->urlGenerator->renderLastAction();
    }

    public function getRemoveFilterUrl(EngineFilter $engineFilter) {
        /** @var FilterRenderer $filterRenderer */
        $filterRenderer = $this->getChildBlock('filter_renderer');

        return $filterRenderer->getRemoveItemUrl($engineFilter);
    }


    public function getRemoveFilterLinkAttributes($filter) {
        return $this->urlGenerator->renderLastAction();
    }

    /**
     * @return EngineFilter[]
     */
    public function getFilters() {
        foreach ($this->engine->getFiltersToShowIn($this->getData('position')) as $engineFilter) {
            if ($engineFilter->isVisible() && $this->isFilterVisible($engineFilter)) {
                yield $engineFilter;
            }
        }
    }

    /**
     * @return EngineFilter[]
     */
    public function getAppliedFilters() {
        foreach ($this->engine->getFilters() as $engineFilter) {
            if ($engineFilter->isApplied() && $this->isAppliedFilterVisibleAsSpecifiedInLayoutXml($engineFilter)) {
                yield $engineFilter;
            }
        }
    }

    public function renderFilter(EngineFilter $engineFilter) {
        /* @var $filterRenderer FilterRenderer */
        $filterRenderer = $this->getChildBlock('filter_renderer');

        return $filterRenderer->render($engineFilter);
    }

    /**
     * @return int
     */
    public function getAppliedOptionCount() {
        $count = 0;
        foreach ($this->getAppliedFilters() as $engineFilter) {
            foreach ($engineFilter->getAppliedItems() as $item) {
                $count++;
            }
        }

        return $count;
    }

    public function renderAppliedItem(EngineFilter $engineFilter, $item) {
        /* @var $appliedItemRenderer AppliedItemRenderer */
        $appliedItemRenderer = $this->getChildBlock('applied_item_renderer');

        return $appliedItemRenderer->render($engineFilter, $item);
    }

    /**
     * @return bool
     */
    public function isAppliedFilterVisible() {
        return $this->config->isAppliedFilterVisible($this->getData('position'));
    }

    public function hide($name) {
        $this->visibility[$name] = false;
    }

    public function show($name) {
        $this->visibility[$name] = true;
    }

    public function hideAll() {
        $this->defaultVisibility = false;
    }

    public function showAll() {
        $this->defaultVisibility = true;
    }

    public function hideAppliedFilter($name) {
        $this->appliedFilterVisibility[$name] = false;
    }

    public function showAppliedFilter($name) {
        $this->appliedFilterVisibility[$name] = true;
    }

    public function hideAllAppliedFilters() {
        $this->defaultAppliedFilterVisibility = false;
    }

    public function showAllAppliedFilters() {
        $this->defaultAppliedFilterVisibility = true;
    }

    public function setFilterPosition($filter, $position) {
        $this->engine->setFilterPosition($filter, $position);
    }

    /**
     * @param EngineFilter $engineFilter
     * @return bool
     */
    protected function isFilterVisible($engineFilter) {
        if (isset($this->visibility[$engineFilter->getFilter()->getData('param_name')])) {
            if ($this->visibility[$engineFilter->getFilter()->getData('param_name')] === false) {
                return false;
            }
        }
        elseif (!$this->defaultVisibility) {
            return false;
        }

        return true;
    }

    /**
     * @param EngineFilter $engineFilter
     * @return bool
     */
    protected function isAppliedFilterVisibleAsSpecifiedInLayoutXml($engineFilter) {
        if (isset($this->appliedFilterVisibility[$engineFilter->getFilter()->getData('param_name')])) {
            if ($this->appliedFilterVisibility[$engineFilter->getFilter()->getData('param_name')] === false) {
                return false;
            }
        }
        elseif (!$this->defaultAppliedFilterVisibility) {
            return false;
        }

        return true;
    }
}