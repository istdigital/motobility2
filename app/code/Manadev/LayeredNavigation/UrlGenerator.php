<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation;

use Magento\Framework\UrlInterface;
use Manadev\Core\Exceptions\NotImplemented;

class UrlGenerator
{
    protected $lastAction = [];

    /**
     * @var Engine
     */
    protected $engine;
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;
    /**
     * @var UrlSettings
     */
    protected $urlSettings;

    public function __construct(Engine $engine, UrlInterface $urlBuilder, UrlSettings $urlSettings) {
        $this->engine = $engine;
        $this->urlBuilder = $urlBuilder;
        $this->urlSettings = $urlSettings;
    }

    /**
     * @param EngineFilter[] $filtersNotToBeCleared
     * @return string
     */
    public function getClearAllUrl($filtersNotToBeCleared) {
        $queryParameters = ['p' => null];
        foreach ($this->engine->getAppliedFilters() as $engineFilter) {
            if (in_array($engineFilter, $filtersNotToBeCleared)) {
                continue;
            }

            $queryParameters[$engineFilter->getFilter()->getData('param_name')] = null;
        }

        $this->lastAction = $queryParameters;
        return $this->getUrl($queryParameters);
    }

    /**
     * @param EngineFilter $engineFilter
     * @param $item
     * @return string
     */
    public function getAddItemUrl(EngineFilter $engineFilter, $item) {
        $combinedValues = $engineFilter->getAppliedOptions() ?: [];

        $this->lastAction = [];
        if (!in_array($item['value'], $combinedValues)) {
            $combinedValues[] = $item['value'];
            $this->lastAction['+' . $engineFilter->getFilter()->getData('param_name')] = $item['value'];
        }

        $combinedValues = implode($this->urlSettings->getMultipleValueSeparator(), $combinedValues);

        $this->lastAction['p'] = '';
        return $this->getUrl([
            $engineFilter->getFilter()->getData('param_name') => $combinedValues,
            'p' => null,
        ]);
    }

    public function getMarkRangeUrl(EngineFilter $engineFilter){
        $rangePattern = "__0__-__1__";
        return $this->getUrl([
            $engineFilter->getFilter()->getData('param_name') => $rangePattern,
            'p' => null,
        ], false);
    }

    public function getMarkAddItemUrl(EngineFilter $engineFilter) {
        return $this->getUrl([
            $engineFilter->getFilter()->getData('param_name') => "__0__",
            'p' => null,
        ], false);
    }

    /**
     * @param EngineFilter $engineFilter
     * @param $item
     * @return string
     */
    public function getRemoveItemUrl(EngineFilter $engineFilter, $item = null) {
        $combinedValues = $engineFilter->getAppliedOptions() ?: [];
        if (!is_array($combinedValues)) {
            $combinedValues = [$combinedValues];
        }

        $this->lastAction = [];
        if(is_null($item) ||isset($item['available_values'])) {
            $combinedValues = [];
            $this->lastAction[$engineFilter->getFilter()->getData('param_name')] = '';
        }
        elseif (($index = array_search($item['value'], $combinedValues)) !== false) {
            unset($combinedValues[$index]);
            $this->lastAction['-' . $engineFilter->getFilter()->getData('param_name')] = $item['value'];
        }

        if (!count($combinedValues)) {
            $combinedValues = null;
        }
        else {
            $combinedValues = implode($this->urlSettings->getMultipleValueSeparator(), $combinedValues);
        }

        $this->lastAction['p']  = '';
        return $this->getUrl([
            $engineFilter->getFilter()->getData('param_name') => $combinedValues,
            'p' => null,
        ]);
    }

    protected function getUrl($queryParameters, $escape = true) {
        return $this->urlBuilder->getUrl('*/*/*', [
            '_current' => true,
            '_use_rewrite' => true,
            '_query' => $queryParameters,
            '_escape' => $escape,
        ]);
    }

    public function getReplaceItemUrl(EngineFilter $engineFilter, $item) {
        $this->lastAction = [
            $engineFilter->getFilter()->getData('param_name') => $item['value'],
            'p' => '',
        ];
        return $this->getUrl([
            $engineFilter->getFilter()->getData('param_name') => $item['value'],
            'p' => null,
        ]);
    }

    public function getLastAction() {
        return $this->lastAction;
    }

    public function renderLastAction() {
        $result = '';

        foreach ($this->getLastAction() as $key => $value) {
            if ($result) {
                $result .= '&';
            }

            $result .= "$key=$value";
        }

        return "data-action=\"$result\"";
    }
}