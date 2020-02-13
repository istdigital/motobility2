<?php

namespace TemplateMonster\ShopByBrand\Model\ResourceModel\Brand\Relation\Product;

use TemplateMonster\ShopByBrand\Model\ResourceModel\Brand as ResourceBrand;
use TemplateMonster\ShopByBrand\Api\Data;
use Magento\Catalog\Model\Indexer\Product\Eav as IndexerEav;
use Magento\Framework\Model\Entity\ScopeResolver;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Eav\Model\ResourceModel\AttributePersistor;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class SaveHandler
 *
 * @package TemplateMonster\ShopByBrand\Model\ResourceModel\Brand\Relation\Product
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var MetadataPool
     */
    protected $_metadataPool;

    /**
     * @var ResourceBrand
     */
    protected $_resourceBrand;

    /**
     * @var AttributePersistor
     */
    protected $_attributePersistor;

    /**
     * @var ProductCollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var ScopeResolver
     */
    protected $_scopeResolver;

    /**
     * @var IndexerEav
     */
    protected $_indexerEav;

    /**
     * SaveHandler constructor.
     *
     * @param MetadataPool             $metadataPool
     * @param ResourceBrand            $resourceBrand
     * @param AttributePersistor       $attributePersistor
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ScopeResolver            $scopeResolver
     * @param IndexerEav               $indexerEav
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceBrand $resourceBrand,
        AttributePersistor $attributePersistor,
        ProductCollectionFactory $productCollectionFactory,
        ScopeResolver $scopeResolver,
        IndexerEav $indexerEav
    ) {
        $this->_metadataPool = $metadataPool;
        $this->_resourceBrand = $resourceBrand;
        $this->_attributePersistor = $attributePersistor;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_scopeResolver = $scopeResolver;
        $this->_indexerEav = $indexerEav;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $this->_assignProductsToBrand($entity);
        $this->_setProductsBrandAttribute($entity);
    }

    /**
     * Assign products
     *
     * @param $entity
     *
     * @return $this
     * @throws \Exception
     */
    protected function _assignProductsToBrand($entity)
    {
        $entityMetadata = $this->_metadataPool->getMetadata(Data\BrandInterface::class);
        $connection = $entityMetadata->getEntityConnection();
        $brandProductTable = $this->_resourceBrand->getTable('tm_brand_product');

        $brandId = $entity->getId();
        $storeId = 0;

        if (!$brandId) {
            return $this;
        }

        $condition = ['brand_id = ?' => (int)$brandId];
        $connection->delete($brandProductTable, $condition);

        $insertData = [];
        $productIds = $entity->getBrandProducts();
        if ($productIds && is_string($productIds)) {
            $productIds = json_decode($productIds, true);
                foreach($productIds as $productId) {
                    $insertData[] = [
                        'brand_id' => $brandId,
                        'product_id' => $productId,
                        'store_id' => $storeId
                    ];
            }
        }
        if($insertData) {
            $connection->insertMultiple($brandProductTable, $insertData);
        }
    }

    /**
     * Set products brand attribute.
     *
     * @param $entity
     */
    protected function _setProductsBrandAttribute($entity)
    {
        if (!($brandId = $entity->getId())) {
            return;
        }

        $oldProductIds = $this->_getOldProductIds($brandId);
        $newProductIds = $this->_newNewProductIds($entity);

        if ($oldProductIds) {
            foreach ($oldProductIds as $productId) {
                $this->_attributePersistor->registerDelete(
                    'Magento\Catalog\Api\Data\ProductInterface',
                    $productId,
                    'brand_id'
                );
            }
        }

        if ($newProductIds) {
            foreach ($newProductIds as $productId) {
                $this->_attributePersistor->registerInsert(
                    ProductInterface::class,
                    $productId,
                    'brand_id',
                    $brandId
                );
            }
        }

        $ids = $oldProductIds + $newProductIds;

        if (!empty($ids)) {
            $context = $this->_scopeResolver->getEntityContext(ProductInterface::class, [
                'store_id' => 0
            ]);
            $this->_attributePersistor->flush(ProductInterface::class, $context);

            $this->_indexerEav->execute($oldProductIds + $newProductIds);
        }
    }

    /**
     * @param int $brandId
     *
     * @return array
     */
    protected function _getOldProductIds($brandId)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToFilter('brand_id', $brandId);
        $collection->load();

        return array_combine($collection->getLoadedIds(), $collection->getLoadedIds());
    }

    /**
     * @param $entity
     *
     * @return mixed
     */
    protected function _newNewProductIds($entity)
    {
        if ($entity->getBrandProducts()) {
            return array_map('intval', json_decode($entity->getBrandProducts(), true));
        }

        return [];
    }
}