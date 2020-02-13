<?php

namespace TemplateMonster\ShopByBrand\Model\ResourceModel\Brand;

use TemplateMonster\ShopByBrand\Api\Data\BrandInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use \Magento\Store\Model\StoreManagerInterface;


class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'brand_id';

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('TemplateMonster\ShopByBrand\Model\Brand', 'TemplateMonster\ShopByBrand\Model\ResourceModel\Brand');
    }

    public function __construct(
        StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    )
    {
        $this->_storeManager = $storeManager;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Add enabled filter.
     *
     * @return $this
     */
    public function addEnabledFilter()
    {
        return $this->addFieldToFilter('status', BrandInterface::STATUS_ENABLED);
    }

    /**
     * Add website filter.
     *
     * @return $this
     */
    public function addWebsiteFilter($websiteId = null)
    {
        return $this->addFieldToFilter('website_id', $this->_storeManager->getWebsite($websiteId)->getId());
    }

}