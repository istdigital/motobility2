<?php

namespace TemplateMonster\Parallax\Model\ResourceModel\Block\Relation\Store;

use TemplateMonster\Parallax\Model\ResourceModel\Block;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class ReadHandler.
 */
class ReadHandler implements ExtensionInterface
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var Block
     */
    protected $resourceBlock;

    /**
     * @param MetadataPool $metadataPool
     * @param Block        $resourceBlock
     */
    public function __construct(
        MetadataPool $metadataPool,
        Block $resourceBlock
    ) {
        $this->metadataPool = $metadataPool;
        $this->resourceBlock = $resourceBlock;
    }

    /**
     * @param object $entity
     * @param array  $arguments
     *
     * @return object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->getId()) {
            $stores = $this->resourceBlock->lookupStoreIds((int) $entity->getId());
            $entity->setData('store_id', $stores);
        }

        return $entity;
    }
}
