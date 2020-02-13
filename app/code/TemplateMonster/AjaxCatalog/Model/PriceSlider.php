<?php
namespace TemplateMonster\AjaxCatalog\Model;

use \Magento\CatalogSearch\Model\Price\Interval;
use \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price;

class PriceSlider extends Interval
{
    public function __construct(
        Price $resource)
    {
        $this->_resource = $resource;

        parent::__construct($resource);
    }

    /**
     * @return array
     */
    public function getAllPrices()
    {
        return $this->_resource->loadPrices(0);
    }

    /**
     * @return mixed
     */
    public function getMaxPrice()
    {
        return max($this->getAllPrices());
    }

    /**
     * @return mixed
     */
    public function getMinPrice()
    {
        return min($this->getAllPrices());
    }

}