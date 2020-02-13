<?php

namespace TemplateMonster\ShopByBrand\Model\ResourceModel\Brand\Relation\Product;

use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use TemplateMonster\ShopByBrand\Model\ResourceModel\Brand;
use TemplateMonster\ShopByBrand\Api\Data;

class ReadHandler implements ExtensionInterface
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var Brand
     */
    protected $resourceBrand;

    /**
     * @param MetadataPool $metadataPool
     * @param Page $resourcePage
     */
    public function __construct(
        MetadataPool $metadataPool,
        Brand $resourceBrand
    ) {
        $this->metadataPool = $metadataPool;
        $this->resourceBrand = $resourceBrand;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $productIds = $this->lookupProductIds($entity);
        if ($productIds) {
            $preparedArr = [];
            foreach($productIds as $productId) {
                $preparedArr[$productId] = $productId;
            }

            $entity->setProductIds($preparedArr);
        }
    }


    public function lookupProductIds($brand)
    {
        $entityMetadata = $this->metadataPool->getMetadata(Data\BrandInterface::class);
        $connection = $entityMetadata->getEntityConnection();
        $brandProductTable = $this->resourceBrand->getTable('tm_brand_product');
        $brandId = $brand->getId();

        $select = $connection->select()
            ->from($brandProductTable, 'product_id')
            ->where($entityMetadata->getIdentifierField() . ' = :brand_id');

        return $connection->fetchCol($select, ['brand_id' => (int)$brandId]);
    }

}