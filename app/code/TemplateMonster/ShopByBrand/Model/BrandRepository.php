<?php
/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\ShopByBrand\Model;

use TemplateMonster\ShopByBrand\Api\Data;
use TemplateMonster\ShopByBrand\Api\BrandRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use TemplateMonster\ShopByBrand\Model\ResourceModel\Brand as ResourceBrand;
use TemplateMonster\ShopByBrand\Model\ResourceModel\Brand\CollectionFactory as BrandCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class BrandRepository implements  BrandRepositoryInterface
{

    /**
     * @var ResourceBrand
     */
    protected $resource;

    /**
     * @var BrandFactory
     */
    protected $brandFactory;

    /**
     * @var BrandCollectionFactory
     */
    protected $brandCollectionFactory;

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
     * @var Data\BrandInterfaceFactory
     */
    protected $dataBrandFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;


    /**
     * BrandRepository constructor.
     * @param ResourceBrand $resource
     * @param BrandFactory $brandFactory
     * @param Data\BrandInterfaceFactory $dataBrandFactory
     * @param BrandCollectionFactory $brandCollectionFactory
     * @param Data\BrandSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceBrand $resource,
        BrandFactory $brandFactory,
        Data\BrandInterfaceFactory $dataBrandFactory,
        BrandCollectionFactory $brandCollectionFactory,
        Data\BrandSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->brandFactory = $brandFactory;
        $this->brandCollectionFactory = $brandCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataBrandFactory = $dataBrandFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Data\BrandInterface $brand
     * @return Data\BrandInterface
     * @throws CouldNotSaveException
     */
    public function save(\TemplateMonster\ShopByBrand\Api\Data\BrandInterface $brand)
    {
        if (empty($brand->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $brand->setStoreId($storeId);
        }
        try {
            $brand->setUrlPage(strtolower($brand->getUrlPage()));
            $this->resource->save($brand);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the brand: %1',
                $exception->getMessage()
            ));
        }
        return $brand;
    }

    /**
     * @param int $brandId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getById($brandId)
    {
        $brand = $this->brandFactory->create();
        $this->resource->load($brand,$brandId);
        if (!$brand->getId()) {
            throw new NoSuchEntityException(__('Brand with id "%1" does not exist.', $brandId));
        }
        return $brand;
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return mixed
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $collection = $this->brandCollectionFactory->create();
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
        $brands = [];
        /** @var Brand $brandModel */
        foreach ($collection as $brandModel) {
            $brandData = $this->dataBrandFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $brandData,
                $brandModel->getData(),
                'TemplateMonster\ShopByBrand\Api\Data\BrandInterface'
            );
            $brands[] = $this->dataObjectProcessor->buildOutputDataArray(
                $brandData,
                'TemplateMonster\ShopByBrand\Api\Data\BrandInterface'
            );
        }
        $searchResults->setItems($brands);
        return $searchResults;
    }

    /**
     * @param Data\BrandInterface $brand
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(\TemplateMonster\ShopByBrand\Api\Data\BrandInterface $brand)
    {
        try {
            $this->resource->delete($brand);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the brand: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @param int $brandId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($brandId)
    {
        return $this->delete($this->getById($brandId));
    }

}