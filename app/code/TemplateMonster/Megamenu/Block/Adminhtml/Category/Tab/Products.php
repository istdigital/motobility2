<?php
namespace TemplateMonster\Megamenu\Block\Adminhtml\Category\Tab;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;

use TemplateMonster\Megamenu\Helper\Data;

class Products extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_coreRegistry = null;
    protected $_productFactory;
    private $_helper;
    private $_position;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\Registry $coreRegistry,
        Data $helper,
        array $data = []
    ) {
        $this->_productFactory = $productFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_helper = $helper;
        parent::__construct($context, $backendHelper, $data);
    }


    protected function _construct()
    {
        parent::_construct();
        $this->setId('catalog_category_megamenu_products');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }


    public function getCategory()
    {
        return $this->_coreRegistry->registry('category');
    }

    protected function getRouteCategory()
    {
        $parents = $this->getCategory()->getParentIds();
        if(isset($parents[1])) {
            $routeCategory = $parents[1];
        } else {
            $routeCategory = '';
        }
        $result = $this->_categoryFactory->create()->load($routeCategory);

        return $result;
    }

    protected function getSiblings()
    {
        return $this->getRouteCategory()->getAllChildren(true);
    }

    protected function getCategoriesFilterArray()
    {
        $result['eq'] = implode(', ', $this->getSiblings());

        return $result;
    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in category flag
        if ($column->getId() == 'in_megamenu') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
            } elseif (!empty($productIds)) {
                $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $productIds]);
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _prepareCollection()
    {
        if ($this->getCategory()->getId()) {
            $this->setDefaultFilter(['in_category' => 1]);
        }

        $collection = $this->_productFactory->create()->getCollection();

        $collection->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'sku'
        )->addAttributeToSelect(
            'price'
        )->addCategoriesFilter(
            $this->getCategoriesFilterArray()
        );

        $select = $collection->getSelect();
        if ($mmProducts = $this->getCategory()->getMmProducts()) {
            if ($mmProducts != '{}') {
                $products = json_decode($mmProducts, true);
                foreach ($products as $key => $value) {
                    $value = (int)$value;
                    $relevances[] = "SELECT {$key} as product_id, {$value} as position";
                }
                $sql = implode(' UNION ', $relevances);
                $select->joinLeft(
                    ['temp' => new \Zend_Db_Expr("({$sql})")],
                    'temp.product_id = e.entity_id'
                );
            }
        }

        $this->setCollection($collection);

        /*if ($this->_position) {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
        }*/

        if ($this->getCategory()->getProductsReadonly()) {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
        }

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        if (!$this->getCategory()->getProductsReadonly()) {
            $this->addColumn(
                'in_megamenu',
                [
                    'type' => 'checkbox',
                    'name' => 'in_megamenu',
                    'values' => $this->_getSelectedProducts(),
                    'index' => 'entity_id',
                    'header_css_class' => 'col-select col-massaction',
                    'column_css_class' => 'col-select col-massaction'
                ]
            );
        }
        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn('name', ['header' => __('Name'), 'index' => 'name']);
        $this->addColumn('sku', ['header' => __('SKU'), 'index' => 'sku']);
        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'type' => 'currency',
                'currency_code' => (string)$this->_scopeConfig->getValue(
                    \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                ),
                'index' => 'price'
            ]
        );
        $this->addColumn(
            'position',
            [
                'header' => __('Position'),
                'type' => 'number',
                'index' => 'position',
                'editable' => !$this->getCategory()->getProductsReadonly(),
                'filter_index' => 'temp.position',
                'filter'    => false,
                'sortable'  => false
            ]
        );

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('megamenu/category/grid', ['_current' => true]);
    }

    protected function _getSelectedProducts()
    {
        $products = $this->getRequest()->getPost('selected_products');
        if ($products === null) {
            if ($mmProducts = $this->getCategory()->getMmProducts()) {
                $products = json_decode($mmProducts, true);
                return array_keys($products);
            }
        }
        return $products;
    }

    public function getProductWidgetScripts()
    {
        $gridJsObject = $this->getJsObjectName();
        $js = '
<script type="text/javascript">
require([
    "mage/adminhtml/grid"
], function(){
    var hiddenInput = new Element("input", {name:"megamenu_products",id:"in_megamenu_products",value:"", type:"hidden"});
    var categoryEditForm = $("mm_modal_product_content");
    if (categoryEditForm != undefined) {
        categoryEditForm.down().insert({
            after : hiddenInput
        });
    }
    var categoryProducts = $H({});
    $("in_megamenu_products").value = Object.toJSON(categoryProducts);

    function registerCategoryProduct(grid, element, checked){
        if(checked){
            if(element.positionElement){
                element.positionElement.disabled = false;
                categoryProducts.set(element.value, element.positionElement.value);
            }
        }
        else{
            if(element.positionElement){
                element.positionElement.disabled = true;
            }
            categoryProducts.unset(element.value);
        }
        $("in_megamenu_products").value = Object.toJSON(categoryProducts);
        grid.reloadParams = {"selected_products[]":categoryProducts.keys()};
    }

    function categoryProductRowClick(grid, event){
        var trElement = Event.findElement(event, "tr");
        var isInput   = Event.element(event).tagName == "INPUT";
        if(trElement){
            var checkbox = Element.getElementsBySelector(trElement, "input");
            if(checkbox[0]){
                var checked = isInput ? checkbox[0].checked : !checkbox[0].checked;
                '.$gridJsObject.'.setCheckboxChecked(checkbox[0], checked);
            }
        }
    }

    function positionChange(event){
        var element = Event.element(event);
        if(element && element.checkboxElement && element.checkboxElement.checked){
            categoryProducts.set(element.checkboxElement.value, element.value);
            $("in_megamenu_products").value = Object.toJSON(categoryProducts);
        }
    }

    var tabIndex = 1000;
    function categoryProductRowInit(grid, row){
        var checkbox = $(row).getElementsByClassName("checkbox")[0];
        var position = $(row).getElementsByClassName("input-text")[0];
        if(checkbox && position){
            checkbox.positionElement = position;
            position.checkboxElement = checkbox;
            position.disabled = !checkbox.checked;
            position.tabIndex = tabIndex++;
            Event.observe(position,"keyup",positionChange);
        }
    }


        '.$gridJsObject.'.rowClickCallback = categoryProductRowClick;
        '.$gridJsObject.'.initRowCallback = categoryProductRowInit;
        '.$gridJsObject.'.checkboxCheckCallback = registerCategoryProduct;
        if('.$gridJsObject.'.rows) {
            '.$gridJsObject.'.rows.each(function(row){categoryProductRowInit('.$gridJsObject.', row)});
        }
});
</script>
        ';
        return $js;
    }
}
