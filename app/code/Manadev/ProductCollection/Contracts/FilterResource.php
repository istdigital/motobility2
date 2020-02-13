<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\ProductCollection\Contracts;

use Magento\Framework\DB\Select;
use Magento\Store\Model\StoreManagerInterface;
use Manadev\ProductCollection\Factory;
use Magento\Framework\Model\ResourceModel\Db;
use Manadev\ProductCollection\Resources\HelperResource;

abstract class FilterResource extends Db\AbstractDb implements SupportedFilter
{
    const SQL_TRUE = '1 = 1';

    /**
     * @var Factory
     */
    protected $factory;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var HelperResource
     */
    protected $helperResource;

    public function __construct(Db\Context $context, Factory $factory,
        StoreManagerInterface $storeManager, HelperResource $helperResource,
        $resourcePrefix = null)
    {
        $this->storeManager = $storeManager;
        parent::__construct($context, $resourcePrefix);
        $this->factory = $factory;
        $this->helperResource = $helperResource;
    }
    /**
     * @param Filter $filter
     * @return bool
     */
    public function supports(Filter $filter) {
        return true;
    }

    protected function getStoreId() {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * @param Select $select
     * @param Filter $filter
     * @param $callback
     * @return false|string
     */
    abstract public function apply(Select $select, Filter $filter, $callback);

    public function getSignature(Filter $filter, $callback) {
        return $filter->getFullName();
    }
}