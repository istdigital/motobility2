<?php
/** 
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation;

use Magento\Framework\ObjectManagerInterface;
use Manadev\LayeredNavigation\Contracts\FilterTemplate;
use Manadev\LayeredNavigation\Contracts\FilterType;
use Manadev\LayeredNavigation\Models\Filter;

class Factory {
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param EngineFactories $engineFactories
     * @param VisualFilterClasses $visualFilterClasses
     */
    public function __construct(ObjectManagerInterface $objectManager) {

        $this->objectManager = $objectManager;
    }

    /**
     * @return \Manadev\LayeredNavigation\Resources\Collections\FilterCollection
     */
    public function createFilterCollection() {
        return $this->objectManager->create('Manadev\LayeredNavigation\Resources\Collections\FilterCollection');
    }

    /**
     * @param Engine $engine
     * @param Filter $filter
     * @param FilterType $filterType
     * @param FilterTemplate $filterTemplate
     * @return EngineFilter
     */
    public function createEngineFilter(Engine $engine, Filter $filter, FilterType $filterType,
        FilterTemplate $filterTemplate)
    {
        return $this->objectManager->create('Manadev\LayeredNavigation\EngineFilter',
            compact('engine', 'filter', 'filterType', 'filterTemplate'));
    }

    public function createMagentoItem() {
        $item = $this->objectManager->create('Magento\Catalog\Model\Layer\Filter\Item');

        $item->setFilter(null)
            ->setLabel('label')
            ->setValue('value')
            ->setCount(1);

        return $item;
    }
}