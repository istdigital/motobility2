<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\ProductCollection\Contracts;

use Magento\Framework\DB\Select;
use Magento\Store\Model\StoreManagerInterface;
use Manadev\ProductCollection\Configuration;
use Manadev\ProductCollection\Factory;
use Magento\Framework\Model\ResourceModel\Db;
use Manadev\ProductCollection\Resources\HelperResource;

abstract class FacetResource extends Db\AbstractDb
{
    /**
     * @var Factory
     */
    protected $factory;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var Configuration
     */
    protected $configuration;
    /**
     * @var HelperResource
     */
    protected $helperResource;

    public function __construct(Db\Context $context, Factory $factory,
        StoreManagerInterface $storeManager, Configuration $configuration, HelperResource $helperResource,
        $resourcePrefix = null)
    {
        parent::__construct($context, $resourcePrefix);
        $this->factory = $factory;
        $this->storeManager = $storeManager;
        $this->configuration = $configuration;
        $this->helperResource = $helperResource;
    }

    /**
     * @param Facet $facet
     * @return string|null
     */
    public function getBatchType($facet) {
        return null;
    }

    protected function getStoreId() {
        return $this->storeManager->getStore()->getId();
    }

    abstract public function getFilterCallback(Facet $facet);

    /**
     * @param Select $select
     * @param Facet  $facet
     *
     * @return mixed
     */
    abstract public function count(Select $select, Facet $facet);

    /**
     * @param Select $select
     * @param Facet  $facet
     *
     * @return mixed
     */
    public function getSelectedData(Select $select, Facet $facet) {
        return false;
    }

    public function isPreparationStepNeeded() {
        return false;
    }

    public function getPreparationFilterCallback(Facet $facet) {
        return null;
    }

    public function useDirectSelect() {
        return false;
    }

    public function prepare(Select $select, Facet $facet) {
    }

    public function sort(Facet $facet) {
    }
}