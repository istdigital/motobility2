<?php

namespace TemplateMonster\Parallax\Model;

use TemplateMonster\Parallax\Model\ResourceModel\Block as BlockResourceModel;
use TemplateMonster\Parallax\Model\ResourceModel\Block\CollectionFactory as BlockCollectionFactory;
use TemplateMonster\Parallax\Api\Data\BlockInterface;
use TemplateMonster\Parallax\Api\Data\BlockInterfaceFactory;
use TemplateMonster\Parallax\Api\Data\BlockSearchResultsInterfaceFactory;
use TemplateMonster\Parallax\Api\BlockRepositoryInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Block repository implementation.
 *
 * @package TemplateMonster\Parallax\Model
 */
class BlockRepository implements BlockRepositoryInterface
{
    /**
     * @var BlockResourceModel
     */
    protected $_resourceModel;

    /**
     * @var BlockFactory
     */
    protected $_blockFactory;

    /**
     * @var ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var BlockSearchResultsInterfaceFactory
     */
    protected $_searchResultsFactory;

    /**
     * @var BlockCollectionFactory
     */
    protected $_blockCollectionFactory;

    /**
     * @var BlockInterfaceFactory
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
     * BlockRepository constructor.
     *
     * @param BlockResourceModel                 $resourceModel
     * @param BlockFactory                       $blockFactory
     * @param ManagerInterface                   $eventManager
     * @param BlockSearchResultsInterfaceFactory $searchResultsFactory
     * @param BlockCollectionFactory             $blockCollectionFactory
     * @param BlockInterfaceFactory              $dataBlockFactory
     * @param DataObjectHelper                   $dataObjectHelper
     * @param DataObjectProcessor                $dataObjectProcessor
     */
    public function __construct(
        BlockResourceModel $resourceModel,
        BlockFactory $blockFactory,
        ManagerInterface $eventManager,
        BlockSearchResultsInterfaceFactory $searchResultsFactory,
        BlockCollectionFactory $blockCollectionFactory,
        BlockInterfaceFactory $dataBlockFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->_resourceModel = $resourceModel;
        $this->_blockFactory = $blockFactory;
        $this->_eventManager = $eventManager;
        $this->_searchResultsFactory = $searchResultsFactory;
        $this->_blockCollectionFactory = $blockCollectionFactory;
        $this->_dataBlockFactory = $dataBlockFactory;
        $this->_dataObjectHelper = $dataObjectHelper;
        $this->_dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * Save block entity.
     *
     * @param BlockInterface $block
     * @return Block
     * @throws CouldNotSaveException
     */
    public function save(BlockInterface $block)
    {
        try {
            $this->_resourceModel->save($block);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $block;
    }

    /**
     * Load block by id.
     *
     * @param string $blockId
     * @return BlockInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($blockId)
    {
        $block = $this->_blockFactory->create();
        $this->_resourceModel->load($block, $blockId);
        if (!$block->getId()) {
            throw new NoSuchEntityException(__('Parallax block with id "%s" not found.', $blockId));
        }

        return $block;
    }

    /**
     * Get list of blocks by search criteria.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \TemplateMonster\Parallax\Model\ResourceModel\Block\Collection
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->_searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        $collection = $this->_blockCollectionFactory->create();
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

        $blocks = [];
        /** @var Block $blockModel */
        foreach ($collection as $blockModel) {
            $blockData = $this->_dataBlockFactory->create();
            $this->_dataObjectHelper->populateWithArray(
                $blockData,
                $blockModel->getData(),
                'TemplateMonster\Parallax\Api\Data\BlockInterface'
            );
            $blocks[] = $this->_dataObjectProcessor->buildOutputDataArray(
                $blockData,
                'TemplateMonster\Parallax\Api\Data\BlockInterface'
            );
        }
        $searchResults->setItems($blocks);

        return $searchResults;
    }

    /**
     * Delete block entity.
     *
     * @param  BlockInterface $block
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(BlockInterface $block)
    {
        try {
            $this->_resourceModel->delete($block);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__($e->getMessage()));
        }

        return true;
    }

    /**
     * Delete block by id.
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
     * Create block entity instance.
     *
     * @return BlockInterface
     */
    public function getModelInstance()
    {
        return $this->_blockFactory->create();
    }
}
