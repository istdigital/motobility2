<?php
/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\ShopByBrand\Block\Adminhtml\Brand\Tab;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;

class SoldStatistics extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;


    protected $_soldBrandFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \TemplateMonster\ShopByBrand\Model\SoldBrandFactory $soldBrandFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_soldBrandFactory = $soldBrandFactory;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('sold_statistics');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }

    /**
     * @return array|null
     */
    public function getBrand()
    {
        return $this->_coreRegistry->registry('brand');
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection()
    {

        $brandId = $this->getBrand() ? $this->getBrand()->getId() : 0;

        $collection = $this->_soldBrandFactory->create()->getCollection()
            ->addFieldToSelect('*')
            ->addFieldToFilter('brand_id',$brandId);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'item_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'item_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn('store_id', ['header' => __('Purchased from Store'), 'index' => 'store_id','type'=>'store']);
        $this->addColumn('name', ['header' => __('Name'), 'index' => 'name']);
        $this->addColumn('purchased_date', ['header' => __('Purchased Date'), 'index' => 'purchased_date']);
        $this->addColumn('bill_name', ['header' => __('Bill to Name'), 'index' => 'bill_name']);
        $this->addColumn('ship_name', ['header' => __('Ship to Name'), 'index' => 'ship_name']);
        $this->addColumn('qty', ['header' => __('Qty'), 'index' => 'qty']);
        $this->addColumn('base_amount', [
            'header' => __('Row Total'),
            'index' => 'base_amount',
            'type' => 'currency',
            'currency_code' => (string)$this->_scopeConfig->getValue(
                \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('brand/*/gridSold', ['_current' => true]);
    }
}