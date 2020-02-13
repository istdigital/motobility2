<?php

namespace TemplateMonster\Parallax\Model;


use TemplateMonster\Parallax\Model\ResourceModel\Block as BlockResourceModel;
use TemplateMonster\Parallax\Model\ResourceModel\Block\Item\CollectionFactory as BlockItemCollectionFactory;
use TemplateMonster\Parallax\Api\Data\BlockItemInterfaceFactory;
use TemplateMonster\Parallax\Api\Data\BlockItemSearchResultsInterfaceFactory;
use TemplateMonster\Parallax\Model\ResourceModel\Block\Item as BlockItemResource;
use TemplateMonster\Parallax\Api\Data\BlockItemInterface;
use TemplateMonster\Parallax\Api\BlockItemRepositoryInterface;
use TemplateMonster\Parallax\Model\Block\ItemFactory as BlockItemFactory;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class BlockItemRepository
 *
 * @package TemplateMonster\Parallax\Model
 */
class BlockItemRepository implements BlockItemRepositoryInterface
{
    /**
     * @var BlockItemResource
     */
    protected $_resourceModel;

    /**
     * @var BlockFactory
     */
    protected $_blockItemFactory;

    /**
     * @var ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var BlockItemSearchResultsInterfaceFactory
     */
    protected $_searchResultsFactory;

    /**
     * @var BlockItemCollectionFactory
     */
    protected $_blockItemCollectionFactory;

    /**
     * @var BlockItemInterfaceFactory
     */
    protected $_dataBlockFactory;

    /**
     * @var DataObjectHelper
     */
    protected $_dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $_dataObjectProcessor;

    /**
     * BlockItemRepository constructor.
     *
     * @param BlockItemResource                      $resourceModel
     * @param BlockItemFactory                       $blockItemFactory
     * @param ManagerInterface                       $eventManager
     * @param BlockItemSearchResultsInterfaceFactory $searchResultsFactory
     * @param BlockItemCollectionFactory             $blockItemCollectionFactory
     * @param BlockItemInterfaceFactory              $dataBlockFactory
     * @param DataObjectHelper                       $dataObjectHelper
     * @param DataObjectProcessor                    $dataObjectProcessor
     */
    public function __construct(
        BlockItemResource $resourceModel,
        BlockItemFactory $blockItemFactory,
        ManagerInterface $eventManager,
        BlockItemSearchResultsInterfaceFactory $searchResultsFactory,
        BlockItemCollectionFactory $blockItemCollectionFactory,
        BlockItemInterfaceFactory $dataBlockFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->_resourceModel = $resourceModel;
        $this->_blockItemFactory = $blockItemFactory;
        $this->_eventManager = $eventManager;
        $this->_searchResultsFactory = $searchResultsFactory;
        $this->_blockItemCollectionFactory = $blockItemCollectionFactory;
        $this->_dataBlockFactory = $dataBlockFactory;
        $this->_dataObjectHelper = $dataObjectHelper;
        $this->_dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * Save block item entity.
     *
     * @param BlockItemInterface $blockItem
     *
     * @return BlockItemInterface
     * @throws CouldNotSaveException
     */
    public function save(BlockItemInterface $blockItem)
    {
        try {
            $this->_resourceModel->save($blockItem);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $blockItem;
    }

    /**
     * Load block item by id.
     *
     * @param int $itemId
     *
     * @return Block|Block\Item
     * @throws NoSuchEntityException
     */
    public function getById($itemId)
    {
        $blockItem = $this->_blockItemFactory->create();
        $this->_resourceModel->load($blockItem, $itemId);
        if (!$blockItem->getId()) {
            throw new NoSuchEntityException(__('Parallax block item with id "%s" not found.', $itemId));
        }

        return $blockItem;
    }

    /**
     * Get list of block items by search criteria.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \TemplateMonster\Parallax\Model\ResourceModel\Block\Item\Collection
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->_searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        $collection = $this->_blockItemCollectionFactory->create();
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
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
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        $blockItems = [];
        /** @var Block $blockModel */
        foreach ($collection as $blockModel) {
            $blockData = $this->_dataBlockFactory->create();
            $this->_dataObjectHelper->populateWithArray(
                $blockData,
                $blockModel->getData(),
                'TemplateMonster\Parallax\Api\Data\BlockItemInterface'
            );
            $blockItems[] = $this->_dataObjectProcessor->buildOutputDataArray(
                $blockData,
                'TemplateMonster\Parallax\Api\Data\BlockItemInterface'
            );
        }
        $searchResults->setItems($blockItems);

        return $searchResults;
    }

    /**
     * Delete block item entity.
     *
     * @param BlockItemInterface $blockItem
     *
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(BlockItemInterface $blockItem)
    {
        try {
            $this->_resourceModel->delete($blockItem);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__($e->getMessage()));
        }

        return true;
    }

    /**
     * Delete block item by id.
     *
     * @param string $blockId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($blockId)
    {
        return $this->delete($this->getById($blockId));
    }

    /**
     * Create block item entity instance.
     *
     * @return BlockItemInterface
     */
    public function getModelInstance()
    {
        return $this->_blockItemFactory->create();
    }
}
