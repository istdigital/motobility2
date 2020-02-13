<?php
/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FilmSlider\Model;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use TemplateMonster\FilmSlider\Api\SliderRepositoryInterface;
use TemplateMonster\FilmSlider\Model\ResourceModel\SliderItem as ResourceSliderItem;
use TemplateMonster\FilmSlider\Model\ResourceModel\SliderItem\CollectionFactory as SliderItemCollectionFactory;
use TemplateMonster\FilmSlider\Api\Data\SliderItemSearchResultsInterface;
use TemplateMonster\FilmSlider\Api\Data\SliderItemInterface;
use TemplateMonster\FilmSlider\Api\SliderItemRepositoryInterface;

class SliderItemRepository implements SliderItemRepositoryInterface
{

    /**
     * @var ResourceSliderItem
     */
    protected $resource;

    /**
     * @var SliderItemFactory
     */
    protected $sliderItemFactory;

    /**
     * @var SliderCollectionFactory
     */
    protected $sliderCollectionFactory;

    /**
     * @var SliderSearchResultsInterface
     */
    protected $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \TemplateMonster\FilmSlider\Api\Data\SliderItemInterfaceFactory
     */
    protected $dataSliderItemFactory;

    public function __construct(
        ResourceSliderItem $resource,
        SliderItemFactory $sliderItemFactory,
        \TemplateMonster\FilmSlider\Api\Data\SliderItemInterfaceFactory $dataSliderItemFactory,
        SliderItemCollectionFactory $sliderItemCollectionFactory,
        \TemplateMonster\FilmSlider\Api\Data\SliderItemSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->resource = $resource;
        $this->sliderItemFactory = $sliderItemFactory;
        $this->sliderItemCollectionFactory = $sliderItemCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataSliderItemFactory = $dataSliderItemFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * @param SliderItemInterface $sliderItem
     * @return SliderItemInterface
     * @throws CouldNotSaveException
     */
    public function save(SliderItemInterface $sliderItem)
    {
        try {
            $this->resource->save($sliderItem);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $sliderItem;
    }

    /**
     * @param $sliderItemId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getById($sliderItemId)
    {
        $sliderItem = $this->sliderItemFactory->create();
        $sliderItem->load($sliderItemId);
        if (!$sliderItem->getId()) {
            throw new NoSuchEntityException(__('Slider item with id "%1" does not exist.', $sliderItem));
        }
        return $sliderItem;
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return mixed
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $collection = $this->sliderItemCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $sliderItem = [];
        /** @var Slider $sliderModel */
        foreach ($collection as $sliderItemModel) {
            $sliderItemData = $this->dataSliderItemFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $sliderItemData,
                $sliderItemModel->getData(),
                'TemplateMonster\FilmSlider\Api\Data\SliderItemInterface'
            );
            $sliderItem[] = $this->dataObjectProcessor->buildOutputDataArray(
                $sliderItemData,
                'TemplateMonster\FilmSlider\Api\Data\SliderItemInterface'
            );
        }
        $searchResults->setItems($sliderItem);
        return $searchResults;
    }

    /**
     * @param SliderInterface $slider
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(SliderItemInterface $sliderItem)
    {
        try {
            $this->resource->delete($sliderItem);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @param $sliderId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($sliderItemId)
    {
        return $this->delete($this->getById($sliderItemId));
    }

    /**
     * @return Slider
     */
    public function getModelInstance()
    {
        return $this->sliderItemFactory->create();
    }
}
