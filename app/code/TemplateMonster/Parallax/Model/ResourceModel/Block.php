<?php

namespace TemplateMonster\Parallax\Model\ResourceModel;

use TemplateMonster\Parallax\Api\Data\BlockInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Parallax Block Resource Model.
 */
class Block extends AbstractDb
{
    /**
     * @var MetadataPool
     */
    protected $_metadataPool;

    /**
     * @var EntityManager
     */
    protected $_entityManager;

    /**
     * Block constructor.
     *
     * @param MetadataPool  $metadataPool
     * @param EntityManager $entityManager
     * @param Context       $context
     * @param null          $connectionName
     */
    public function __construct(
        MetadataPool $metadataPool,
        EntityManager $entityManager,
        Context $context,
        $connectionName = null
    ) {
        $this->_metadataPool = $metadataPool;
        $this->_entityManager = $entityManager;
        parent::__construct($context, $connectionName);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('parallax_block', 'block_id');
    }

    /**
     * Perform operations after object load.
     *
     * @param AbstractModel $object
     *
     * @return $this
     */
    protected function _afterLoad(AbstractModel $object)
    {
        if ($object->getId()) {
            $stores = $this->getStoreIds($object->getId());

            $object->setData('store_id', $stores);
        }

        return parent::_afterLoad($object);
    }

    /**
     * Get store ids to which specified item is assigned.
     *
     * @param int $blockId
     *
     * @return array
     */
    public function getStoreIds($blockId)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $this->getTable('parallax_block_store'),
            'store_id'
        )->where(
            'block_id = ?',
            (int) $blockId
        );

        return $connection->fetchCol($select);
    }

    /**
     * Load an object.
     *
     * @param \Magento\Framework\Model|AbstractModel $object
     * @param mixed                                  $value
     * @param string                                 $field  field to load by (defaults to model id)
     *
     * @return $this
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        $blockId = $this->getBlockId($object, $value, $field);
        if ($blockId) {
            $this->_entityManager->load($object, $blockId);
        }

        return $this;
    }

    /**
     * @param AbstractModel $object
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function save(AbstractModel $object)
    {
        $this->_entityManager->save($object);

        return $this;
    }

    /**
     * Get store ids to which specified item is assigned.
     *
     * @param int $blockId
     *
     * @return array
     */
    public function lookupStoreIds($blockId)
    {
        $connection = $this->getConnection();

        $entityMetadata = $this->_metadataPool->getMetadata(BlockInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = $connection->select()
            ->from(['pbs' => $this->getTable('parallax_block_store')], 'store_id')
            ->join(
                ['pb' => $this->getMainTable()],
                'pbs.'.$linkField.' = pb.'.$linkField,
                []
            )
            ->where('pb.'.$entityMetadata->getIdentifierField().' = :block_id');

        return $connection->fetchCol($select, ['block_id' => (int) $blockId]);
    }

    /**
     * @param AbstractModel $object
     * @param mixed         $value
     * @param null          $field
     *
     * @return bool|int|string
     *
     * @throws LocalizedException
     * @throws \Exception
     */
    private function getBlockId(AbstractModel $object, $value, $field = null)
    {
        $entityMetadata = $this->_metadataPool->getMetadata(BlockInterface::class);
        if (!is_numeric($value) && $field === null) {
            $field = 'identifier';
        } elseif (!$field) {
            $field = $entityMetadata->getIdentifierField();
        }
        $entityId = $value;
        if ($field != $entityMetadata->getIdentifierField() || $object->getStoreId()) {
            $select = $this->_getLoadSelect($field, $value, $object);
            $select->reset(Select::COLUMNS)
                ->columns($this->getMainTable().'.'.$entityMetadata->getIdentifierField())
                ->limit(1);
            $result = $this->getConnection()->fetchCol($select);
            $entityId = count($result) ? $result[0] : false;
        }

        return $entityId;
    }

    /**
     * Retrieve select object for load object data.
     *
     * @param string                                 $field
     * @param mixed                                  $value
     * @param \Magento\Cms\Model\Block|AbstractModel $object
     *
     * @return Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $entityMetadata = $this->_metadataPool->getMetadata(BlockInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = parent::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId()) {
            $stores = [(int) $object->getStoreId(), Store::DEFAULT_STORE_ID];

            $select->join(
                ['cbs' => $this->getTable('cms_block_store')],
                $this->getMainTable().'.'.$linkField.' = cbs.'.$linkField,
                ['store_id']
            )
                ->where('is_active = ?', 1)
                ->where('cbs.store_id in (?)', $stores)
                ->order('store_id DESC')
                ->limit(1);
        }

        return $select;
    }
}
