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
use TemplateMonster\FilmSlider\Model\ResourceModel\Slider as ResourceSlider;
use TemplateMonster\FilmSlider\Model\ResourceModel\Slider\CollectionFactory as SliderCollectionFactory;
use TemplateMonster\FilmSlider\Api\Data\SliderSearchResultsInterface;
use TemplateMonster\FilmSlider\Api\Data\SliderInterface;

class SliderRepository implements SliderRepositoryInterface
{
    /**
     * @var ResourceSlider
     */
    protected $resource;

    /**
     * @var SliderFactory
     */
    protected $sliderFactory;

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
     * @var \TemplateMonster\FilmSlider\Api\Data\SliderInterfaceFactory
     */
    protected $dataSliderFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * SliderRepository constructor.
     * @param ResourceSlider $resource
     * @param SliderFactory $sliderFactory
     * @param \TemplateMonster\FilmSlider\Api\Data\SliderInterfaceFactory $dataSliderFactory
     * @param SliderCollectionFactory $sliderCollectionFactory
     * @param SliderSearchResultsInterface $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceSlider $resource,
        SliderFactory $sliderFactory,
        \TemplateMonster\FilmSlider\Api\Data\SliderInterfaceFactory $dataSliderFactory,
        SliderCollectionFactory $sliderCollectionFactory,
        \TemplateMonster\FilmSlider\Api\Data\SliderSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->sliderFactory = $sliderFactory;
        $this->sliderCollectionFactory = $sliderCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataSliderFactory = $dataSliderFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }


    /**
     * @param SliderInterface $slider
     * @return SliderInterface
     * @throws CouldNotSaveException
     */
    public function save(SliderInterface $slider)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $slider->setStoreId($storeId);
        try {
            $this->resource->save($slider);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $slider;
    }

    /**
     * @param $sliderId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getById($sliderId)
    {
        $slider = $this->sliderFactory->create();
        $slider->load($sliderId);
        if (!$slider->getId()) {
            throw new NoSuchEntityException(__('Slider with id "%1" does not exist.', $slider));
        }
        return $slider;
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return mixed
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $collection = $this->sliderCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'store_id') {
                    $collection->addStoreFilter($filter->getValue(), false);
                    continue;
                }
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
        $slider = [];
        /** @var Slider $sliderModel */
        foreach ($collection as $sliderModel) {
            $sliderData = $this->dataSliderFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $sliderData,
                $sliderModel->getData(),
                'TemplateMonster\FilmSlider\Api\Data\SliderInterface'
            );
            $slider[] = $this->dataObjectProcessor->buildOutputDataArray(
                $sliderData,
                'TemplateMonster\FilmSlider\Api\Data\SliderInterface'
            );
        }
        $searchResults->setItems($slider);
        return $searchResults;
    }

    /**
     * @param SliderInterface $slider
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(SliderInterface $slider)
    {
        try {
            $this->resource->delete($slider);
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
    public function deleteById($sliderId)
    {
        return $this->delete($this->getById($sliderId));
    }

    /**
     * @return Slider
     */
    public function getModelInstance()
    {
        return $this->sliderFactory->create();
    }
}
