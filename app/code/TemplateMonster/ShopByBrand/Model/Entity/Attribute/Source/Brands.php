<?php

namespace TemplateMonster\ShopByBrand\Model\Entity\Attribute\Source;

use TemplateMonster\ShopByBrand\Model\ResourceModel\Brand\CollectionFactory;
use Magento\Framework\DB\Ddl\Table;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\ResourceModel\Entity\AttributeFactory;

/**
 * Class Brands
 *
 * @package TemplateMonster\ShopByBrand\Model\Entity\Attribute\Source
 */
class Brands extends AbstractSource
{
    /**
     * @var CollectionFactory
     */
    protected $brandsCollectionFactory;

    /**
     * @var AttributeFactory
     */
    protected $_eavAttrEntity;

    /**
     * Brands constructor.
     *
     * @param CollectionFactory $brandsCollectionFactory
     * @param AttributeFactory  $eavAttrEntity
     */
    public function __construct(
        CollectionFactory $brandsCollectionFactory,
        AttributeFactory $eavAttrEntity
    )
    {
        $this->brandsCollectionFactory = $brandsCollectionFactory;
        $this->_eavAttrEntity = $eavAttrEntity;
    }

    /**
     * Retrieve all options array.
     *
     * @return array
     */
    public function getAllOptions()
    {
        $brandsCollection = $this->brandsCollectionFactory->create();

        if ($this->_options === null) {
            $this->_options[] = ['label' => __('-- Please Select --'), 'value' => ''];
            /** @var \TemplateMonster\ShopByBrand\Api\Data\BrandInterface $brand */
            foreach($brandsCollection as $brand) {
                $this->_options[] = [
                    'label' => __($brand->getName()),
                    'value' => $brand->getBrandId()
                ];
            }
        }

        return $this->_options;
    }

    /**
     * Retrieve option array.
     *
     * @return array
     */
    public function getOptionArray()
    {
        $_options = [];
        foreach ($this->getAllOptions() as $option) {
            $_options[$option['value']] = $option['label'];
        }
        return $_options;
    }

    /**
     * Get a text for option value
     *
     * @param string|int $value
     * @return string|false
     */
    public function getOptionText($value)
    {
        $options = $this->getAllOptions();
        foreach ($options as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }

    /**
     * Retrieve flat column definition.
     *
     * @return array
     */
    public function getFlatColumns()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();

        return [
            $attributeCode => [
                'unsigned' => false,
                'default' => null,
                'extra' => null,
                'type' => Table::TYPE_INTEGER,
                'length' => 11,
                'nullable' => true,
                'comment' => $attributeCode . ' column',
            ],
        ];
    }

    /**
     * Retrieve Indexes(s) for Flat.
     *
     * @return array
     */
    public function getFlatIndexes()
    {
        $indexes = [];

        $index = 'IDX_' . strtoupper($this->getAttribute()->getAttributeCode());
        $indexes[$index] = ['type' => 'index', 'fields' => [$this->getAttribute()->getAttributeCode()]];

        return $indexes;
    }

    /**
     * Retrieve Select For Flat Attribute update.
     *
     * @param int $store
     * @return \Magento\Framework\DB\Select|null
     */
    public function getFlatUpdateSelect($store)
    {
        return $this->_eavAttrEntity->create()->getFlatUpdateSelect($this->getAttribute(), $store);
    }
}