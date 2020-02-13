<?php
/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\ShopByBrand\Model;

use TemplateMonster\ShopByBrand\Api\Data;
use TemplateMonster\ShopByBrand\Api\SoldBrandRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use TemplateMonster\ShopByBrand\Model\ResourceModel\SoldBrand as ResourceSoldBrand;
use TemplateMonster\ShopByBrand\Model\ResourceModel\SoldBrand\CollectionFactory as SoldBrandCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class SoldBrandRepository implements  SoldBrandRepositoryInterface
{

    /**
     * @var ResourceBrand|ResourceSoldBrand
     */
    protected $resource;

    /**
     * @var SoldBrandFactory
     */
    protected $soldBrandFactory;

    /**
     * @var SoldBrandCollectionFactory
     */
    protected $soldBrandCollectionFactory;

    /**
     * @var Data\BrandSearchResultsInterfaceFactory
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
     * @var Data\SoldBrandInterfaceFactory
     */
    protected $dataSoldBrandFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;


    /**
     * SoldBrandRepository constructor.
     * @param ResourceSoldBrand $resource
     * @param SoldBrandFactory $soldBrandFactory
     * @param Data\SoldBrandInterfaceFactory $dataSoldBrandFactory
     * @param SoldBrandCollectionFactory $soldBrandCollectionFactory
     * @param Data\BrandSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceSoldBrand $resource,
        SoldBrandFactory $soldBrandFactory,
        Data\SoldBrandInterfaceFactory $dataSoldBrandFactory,
        SoldBrandCollectionFactory $soldBrandCollectionFactory,
        Data\BrandSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface
    ) {
        $this->resource = $resource;
        $this->soldBrandFactory = $soldBrandFactory;
        $this->soldBrandCollectionFactory = $soldBrandCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataSoldBrandFactory = $dataSoldBrandFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->_timezoneInterface = $timezoneInterface;
    }

    /**
     * @param Data\BrandInterface $brand
     * @return Data\BrandInterface
     * @throws CouldNotSaveException
     */
    public function save(\TemplateMonster\ShopByBrand\Api\Data\SoldBrandInterface $soldBrand)
    {
        try {
            $time = $this->_timezoneInterface->date()->format('m/d/y H:i:s');
            $soldBrand->setPurchasedDate($time);
            $this->resource->save($soldBrand);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the sold brand: %1',
                $exception->getMessage()
            ));
        }
        return $soldBrand;
    }

    /**
     * @param int $soldBrandId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getById($soldBrandId)
    {
        $soldBrand = $this->soldBrandFactory->create();
        $this->resource->load($soldBrand,$soldBrandId);
        if (!$soldBrand->getId()) {
            throw new NoSuchEntityException(__('Sold brand with id "%1" does not exist.', $soldBrandId));
        }
        return $soldBrand;
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return mixed
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $collection = $this->soldBrandCollectionFactory->create();
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
        $soldBrands = [];
        /** @var Brand $soldBrandModel */
        foreach ($collection as $soldBrandModel) {
            $soldBrandData = $this->dataBrandFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $soldBrandData,
                $soldBrandModel->getData(),
                'TemplateMonster\ShopByBrand\Api\Data\SoldBrandInterface'
            );
            $soldBrands[] = $this->dataObjectProcessor->buildOutputDataArray(
                $soldBrandData,
                'TemplateMonster\ShopByBrand\Api\Data\SoldBrandInterface'
            );
        }
        $searchResults->setItems($soldBrands);
        return $searchResults;
    }

    /**
     * @param Data\SoldBrandInterface $soldBrand
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(\TemplateMonster\ShopByBrand\Api\Data\SoldBrandInterface $soldBrand)
    {
        try {
            $this->resource->delete($soldBrand);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the sold brand: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @param int $soldBrandId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($soldBrandId)
    {
        return $this->delete($this->getById($soldBrandId));
    }

}