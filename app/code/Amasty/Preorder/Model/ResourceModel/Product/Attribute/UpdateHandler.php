<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */

namespace Amasty\Preorder\Model\ResourceModel\Product\Attribute;

use Magento\Framework\EntityManager\MetadataPool;
use Magento\Eav\Api\AttributeRepositoryInterface as AttributeRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Model\Entity\ScopeResolver;
use Magento\Eav\Model\ResourceModel\AttributePersistor;
use Magento\Eav\Model\ResourceModel\ReadSnapshot;
use Magento\Eav\Model\ResourceModel\AttributeLoader;
use Magento\Catalog\Api\Data\ProductAttributeInterface;

/**
 * Class UpdateHandler
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateHandler extends \Magento\Eav\Model\ResourceModel\UpdateHandler
{
    /**
     * @var AttributeRepository
     */
    private $attributeRepository;

    public function __construct(
        AttributeRepository $attributeRepository,
        MetadataPool $metadataPool,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributePersistor $attributePersistor,
        ReadSnapshot $readSnapshot,
        ScopeResolver $scopeResolver,
        AttributeLoader $attributeLoader = null
    ) {
        parent::__construct(
            $attributeRepository,
            $metadataPool,
            $searchCriteriaBuilder,
            $attributePersistor,
            $readSnapshot,
            $scopeResolver,
            $attributeLoader
        );
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param string $entityType
     * @return \Magento\Eav\Api\Data\AttributeInterface[]
     */
    protected function getAttributes($entityType, $attributeSetId = null)
    {
        return [
            $this->attributeRepository->get(ProductAttributeInterface::ENTITY_TYPE_CODE, 'amasty_preorder_note'),
            $this->attributeRepository->get(ProductAttributeInterface::ENTITY_TYPE_CODE, 'amasty_preorder_cart_label')
        ];
    }
}
