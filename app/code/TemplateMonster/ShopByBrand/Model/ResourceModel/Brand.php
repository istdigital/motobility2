<?php

namespace TemplateMonster\ShopByBrand\Model\ResourceModel;

use TemplateMonster\ShopByBrand\Api\Data\BrandInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\EntityManager\MetadataPool;

class Brand extends AbstractDb
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * Brand constructor.
     * @param Context $context
     * @param EntityManager $entityManager
     * @param MetadataPool $metadataPool
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        EntityManager $entityManager,
        MetadataPool $metadataPool,
        $connectionName = null
    )
    {
        $this->entityManager = $entityManager;
        $this->metadataPool = $metadataPool;
        parent::__construct($context, $connectionName);
    }

    /**
     * @inheritDoc
     */
    public function getConnection()
    {
        return $this->metadataPool->getMetadata(BrandInterface::class)->getEntityConnection();
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('tm_brand', 'brand_id');
    }

    /**
     * @inheritDoc
     */
    public function save(AbstractModel $object)
    {
        $this->entityManager->save($object);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function delete(AbstractModel $object)
    {
        $this->entityManager->delete($object);
        return $this;
    }

    public function load(\Magento\Framework\Model\AbstractModel $brand, $brandId, $field = null)
    {
        $this->entityManager->load($brand, $brandId);

        return $this;
    }

    protected function _afterSave(\Magento\Framework\Model\AbstractModel $brand)
    {
        return parent::_afterSave($brand);
    }

    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $object->setBrandId(20);
        return $this;
    }

    public function getBrandByProduct(ProductInterface $product)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getTable('tm_brand_product'), 'brand_id')
            ->where('product_id = ?', $product->getId());

        return $connection->fetchOne($select);
    }

    public function getBrandById($id)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getTable('tm_brand'), '*')
            ->where('brand_id = ?', $id);

        return current($connection->fetchAll($select));
    }



    public function checkIdentifier($identifier)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getTable('tm_brand'), 'brand_id')
            ->where('url_key = ? AND status = 1', $identifier);

        return $this->getConnection()->fetchOne($select);
    }

    public function getAssignedProductIds(BrandInterface $brand)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getTable('tm_brand_product'), 'product_id')
            ->where('brand_id = ?', $brand->getId());

        return $connection->fetchCol($select);
    }
}