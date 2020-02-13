<?php
/**
 * Mage-World
 *
 *  @category    Mage-World
 *  @package     MW
 *  @author      Mage-world Developer
 *
 *  @copyright   Copyright (c) 2018 Mage-World (https://www.mage-world.com/)
 */

namespace MW\Downloads\Model;

class Downloads extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'downloads';

    /**
     * Name of object id field
     *
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * AdjustStock constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ){
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('MW\Downloads\Model\ResourceModel\Downloads');
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities()
    {
        return [$this->getId()];
    }

    public function getDownloadsByCategoryId($categoryId)
    {
        return $this->getCollection()->addFieldToFilter('category_id',$categoryId);
    }
}