<?php

namespace TemplateMonster\Parallax\Model;

use Magento\Framework\Api\SortOrder;
use TemplateMonster\Parallax\Model\ResourceModel\Block\Item\CollectionFactory as ItemCollectionFactory;
use TemplateMonster\Parallax\Api\Data\BlockInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Model\AbstractModel;

/**
 * Parallax Block Model.
 */
class Block extends AbstractModel implements BlockInterface
{
    const CACHE_TAG = 'PARALLAX_BLOCK';

    /**
     * @var ItemCollectionFactory
     */
    protected $_itemCollectionFactory;

    /**
     * Block constructor.
     *
     * @param ItemCollectionFactory $itemCollectionFactory
     * @param Context               $context
     * @param Registry              $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null       $resourceCollection
     * @param array                 $data
     */
    public function __construct(
        ItemCollectionFactory $itemCollectionFactory,
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_itemCollectionFactory = $itemCollectionFactory;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('TemplateMonster\Parallax\Model\ResourceModel\Block');
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return (int) $this->getData(self::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, (int) $status);
    }

    /**
     * @inheritdoc
     */
    public function getCssClass()
    {
        return $this->getData(self::CSS_CLASS);
    }

    /**
     * @inheritdoc
     */
    public function setCssClass($cssClass)
    {
        return $this->setData(self::CSS_CLASS, $cssClass);
    }

    /**
     * @inheritdoc
     */
    public function isFullWidth()
    {
        return (bool) $this->getData(self::IS_FULL_WIDTH);
    }

    /**
     * @inheritdoc
     */
    public function setIsFullWidth($fullWidth)
    {
        return $this->setData(self::IS_FULL_WIDTH, (bool) $fullWidth);
    }

    /**
     * @inheritdoc
     */
    public function getStores()
    {
        return $this->hasData('stores') ? $this->getData('stores') : (array) $this->getData('store_id');
    }

    /**
     * Get block items collection.
     *
     * @return ResourceModel\Block\Item\Collection|\TemplateMonster\Parallax\Model\Block\Item[]
     */
    public function getItemsCollection()
    {
        $collection = $this->_itemCollectionFactory->create();
        $collection
            ->addBlockFilter($this)
            ->setOrder('sort_order', SortOrder::SORT_ASC)
        ;

        return $collection;
    }

    /**
     * Get enabled block items collection.
     *
     * @return ResourceModel\Block\Item\Collection|\TemplateMonster\Parallax\Model\Block\Item[]
     */
    public function getEnabledItemsCollection()
    {
        $collection = $this->getItemsCollection();
        $collection->addEnabledFilter();

        return $collection;
    }

    /**
     * Check is enabled.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_isStatus(BlockInterface::STATUS_ENABLED);
    }

    /**
     * Check if disabled.
     *
     * @return bool
     */
    public function isDisabled()
    {
        return $this->_isStatus(BlockInterface::STATUS_DISABLED);

    }

    /**
     * Check if specified status.
     *
     * @param int $status
     *
     * @return bool
     */
    protected function _isStatus($status)
    {
        return $this->getStatus() === $status;
    }
}
