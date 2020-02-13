<?php
/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FeaturedProduct\Block\Adminhtml\Widget;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Chooser extends Extended
{
    /**
     * @var array
     */
    protected $_selectedProducts = [];

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category
     */
    protected $_resourceCategory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $_resourceProduct;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var string
     */
    protected $_template = 'TemplateMonster_FeaturedProduct::widget/grid/extended.phtml';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Category $resourceCategory
     * @param \Magento\Catalog\Model\ResourceModel\Product $resourceProduct
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\ResourceModel\Category $resourceCategory,
        \Magento\Catalog\Model\ResourceModel\Product $resourceProduct,
        array $data = []
    ) {
        $this->_categoryFactory = $categoryFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_resourceCategory = $resourceCategory;
        $this->_resourceProduct = $resourceProduct;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Block construction, prepare grid params
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setDefaultSort('name');
        $this->setUseAjax(true);
    }

    /**
     * Prepare chooser element HTML
     *
     * @param AbstractElement $element Form Element
     * @return AbstractElement
     */
    public function prepareElementHtml(AbstractElement $element)
    {
        $uniqId = $this->mathRandom->getUniqueHash($element->getId());
        $sourceUrl = $this->getUrl('featuredproduct/product_widget/chooser', ['uniq_id' => $uniqId,
                'use_massaction' => true
            ]
        );

        $chooser = $this->getLayout()->createBlock(
            'Magento\Widget\Block\Adminhtml\Widget\Chooser'
        )->setElement(
            $element
        )->setConfig(
            $this->getConfig()
        )->setFieldsetId(
            $this->getFieldsetId()
        )->setSourceUrl(
            $sourceUrl
        )->setUniqId(
            $uniqId
        );

        if ($element->getValue()) {
            $label = __('Products selected');
            $chooser->setLabel($label);
        }

        $element->setData('after_element_html', $chooser->toHtml());
        return $element;
    }

    /**
     * Checkbox Check JS Callback
     *
     * @return string
     */
    public function getCheckboxCheckCallback()
    {
        if ($this->getUseMassaction()) {

            return 'function (grid, element, checked) {
                var productData = $$(".products_ids")[0];
                var elementId = element.value;

                if(elementId == "on") {return;}

                if(element.checked && !productsMap.get(elementId)) {
                    productsMap.set(elementId,elementId);
                } else if(!element.checked && productsMap.get(elementId)) {
                    productsMap.unset(elementId);
                }

                productData.value = JSON.stringify(productsMap);
                $(grid.containerId).fire("product:changed", {element: element});
            }';
        }
    }

    /**
     * Grid Row JS Callback
     *
     * @return string
     */
    public function getRowClickCallback()
    {
        if ($this->getUseMassaction()) {
            $gridJsObject = $this->getJsObjectName();
            return '
                function (grid, event) {
                    var trElement = Event.findElement(event, "tr");
                    var isInput   = Event.element(event).tagName == "INPUT";
                    if(trElement){
                        var checkbox = Element.getElementsBySelector(trElement, "input");
                        if(checkbox[0]){
                            var checked = isInput ? checkbox[0].checked : !checkbox[0].checked;
                            '.$gridJsObject.'.setCheckboxChecked(checkbox[0], checked);
                        }
                    }
                    /**
                    var inputElement = Event.findElement(event, "input");
                    **/
                    var inputElement = checkbox;
                    var elementId  = inputElement.value;

                    if(inputElement.checked && !productsMap.get(elementId)){
                        productsMap.set(elementId,elementId);
                    } else if(productsMap.get(elementId)){
                        productsMap.unset(elementId);
                    }

                    productData.value = JSON.stringify(productsMap);

                }';
        }
    }

    public function getRowInitCallback(){
        return '
            function categoryProductRowInit(grid, row){
                var checkbox = $(row).getElementsByTagName("input")[0];
                var elementId  = checkbox.value;

                if(productsMap.get(elementId))
                {
                    checkbox.checked = true;
                }
            }
        ';
    }

    /**
     * Category Tree node onClick listener js function
     *
     * @return string
     */
    public function getCategoryClickListenerJs()
    {
        $js = '
            function (node, e) {
                {jsObject}.addVarToUrl("category_id", node.attributes.id);
                {jsObject}.reload({jsObject}.url);
                {jsObject}.categoryId = node.attributes.id != "none" ? node.attributes.id : false;
                {jsObject}.categoryName = node.attributes.id != "none" ? node.text : false;
            }
        ';
        $js = str_replace('{jsObject}', $this->getJsObjectName(), $js);
        return $js;
    }

    public function getAdditionalJavaScript()
    {
        $chooserJsObject = $this->getId();
        return '

            varienGrid.prototype.doFilter  = function (callback) {

                var filters = $$(\'#\' + this.containerId + \' [data-role="filter-form"] input\', \'#\' + this.containerId + \' [data-role="filter-form"] select\');
                var elements = [];
                if($$(".products_ids")){
                    elements.push($$(".products_ids")[0]);
                }
                for (var i in filters) {
                    if (filters[i].value && filters[i].value.length) elements.push(filters[i]);
                }

                if (!this.doFilterCallback || (this.doFilterCallback && this.doFilterCallback())) {
                    this.reload(this.addVarToUrl(this.filterVar, Base64.encode(Form.serializeElements(elements))), callback);
                }
            };

            jQuery("body").off("click", "#add_product");
            jQuery("body").on("click","#add_product",function () {
                    var productsString = productData.value;
                    var productsObject = JSON.parse(productsString);
                    var productsArray = Object.keys(productsObject);
                    var commaSeparate = productsArray.join();
                    var label = "Empty";
                    if(commaSeparate) {
                        label = "Product was added";
                    }
                    ' .
                $chooserJsObject .
                '.setElementValue(commaSeparate);
                        ' .
                $chooserJsObject .
                '.setElementLabel(label);
                        ' .
                $chooserJsObject .
                '.close();
                    '.
                $chooserJsObject.'.dialogContent = null;
            });
            ';
    }

    /**
     * Filter checked/unchecked rows in grid
     *
     * @param Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_products') {
            $productsIdsWidgets = $this->getRequest()->getParam('products_ids_widgets',[]);
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productsIdsWidgets]);
            } else {
                $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $productsIdsWidgets]);
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Prepare products collection, defined collection filters (category, product type)
     *
     * @return Extended
     */
    protected function _prepareCollection()
    {
        /* @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_collectionFactory->create()->setStoreId(0)->addAttributeToSelect('name');

        if ($categoryId = $this->getCategoryId()) {
            $category = $this->_categoryFactory->create()->load($categoryId);
            if ($category->getId()) {
                // $collection->addCategoryFilter($category);
                $productIds = $category->getProductsPosition();
                $productIds = array_keys($productIds);
                if (empty($productIds)) {
                    $productIds = 0;
                }
                $collection->addFieldToFilter('entity_id', ['in' => $productIds]);
            }
        }

        if ($productTypeId = $this->getProductTypeId()) {
            $collection->addAttributeToFilter('type_id', $productTypeId);
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare columns for products grid
     *
     * @return Extended
     */
    protected function _prepareColumns()
    {
        if ($this->getUseMassaction()) {
            $this->addColumn(
                'in_products',
                [
                    'header_css_class' => 'a-center',
                    'type' => 'checkbox',
                    'name' => 'in_products',
                    'inline_css' => 'checkbox entities',
                    'field_name' => 'in_products',
                    'values' => $this->getSelectedProducts(),
                    'align' => 'center',
                    'index' => 'entity_id',
                    'use_index' => true
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
        $this->addColumn(
            'chooser_sku',
            [
                'header' => __('SKU'),
                'name' => 'chooser_sku',
                'index' => 'sku',
                'header_css_class' => 'col-sku',
                'column_css_class' => 'col-sku'
            ]
        );
        $this->addColumn(
            'chooser_name',
            [
                'header' => __('Product'),
                'name' => 'chooser_name',
                'index' => 'name',
                'header_css_class' => 'col-product',
                'column_css_class' => 'col-product'
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Adds additional parameter to URL for loading only products grid
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            'featuredproduct/product_widget/chooser',
            [
                'products_grid' => true,
                '_current' => true,
                'uniq_id' => $this->getId(),
                'use_massaction' => $this->getUseMassaction(),
                'product_type_id' => $this->getProductTypeId()
            ]
        );
    }

    /**
     * Setter
     *
     * @param array $selectedProducts
     * @return $this
     */
    public function setSelectedProducts($selectedProducts)
    {
        $this->_selectedProducts = $selectedProducts;
        return $this;
    }

    /**
     * Getter
     *
     * @return array
     */
    public function getSelectedProducts()
    {
        if ($selectedProducts = $this->getRequest()->getParam('selected_products', null)) {
            $this->setSelectedProducts($selectedProducts);
        }
        return $this->_selectedProducts;
    }

    public function getMainButtonsHtml()
    {
        $html = parent::getMainButtonsHtml();

        return $html.$this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'add_product',
                'label' => __('Add Product'),
                'type' => 'button',
                'title' => __('Add Product'),
                'class' => 'add-product'
            ]
        )->toHtml();
    }
}
