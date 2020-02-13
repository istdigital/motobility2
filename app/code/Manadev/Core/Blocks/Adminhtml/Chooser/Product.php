<?php
/**
 * Created by PhpStorm.
 * User: Vernard
 * Date: 8/12/2015
 * Time: 4:34 PM
 */

namespace Manadev\Core\Blocks\Adminhtml\Chooser;

use Magento\Catalog\Block\Adminhtml\Product\Widget\Chooser;

class Product extends Chooser
{
    /**
     * Prepare products collection, defined collection filters (category, product type)
     *
     * @return Extended
     */
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
        parent::__construct($context, $backendHelper, $categoryFactory, $collectionFactory, $resourceCategory, $resourceProduct, $data);
    }

    protected function _prepareCollection() {
        /* @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_collectionFactory->create()
                ->setStoreId(0)
                ->addAttributeToSelect('name');

        if ($categoryId = $this->getCategoryId()) {
            $category = $this->_categoryFactory->create()->load($categoryId);
            if ($category->getId()) {
                // $collection->addCategoryFilter($category);
                $productIds = $category->getProductsPosition();
                $productIds = array_keys($productIds);
                if (empty($productIds)) {
                    $productIds = 0;
                }
                $collection->addFieldToFilter('entity_id', array('in' => $productIds));
            }
        }

        if ($productTypeId = $this->getProductTypeId()) {
            $collection->addAttributeToFilter('type_id', $productTypeId);
        }

        if ($productIds = $this->getHiddenProducts()) {
            $collection->addFieldToFilter('entity_id', array('nin' => explode(',', $productIds)));
        }

        $this->setCollection($collection);
        return $this->_basePrepareCollection();
    }

    /**
     * Adds additional parameter to URL for loading only products grid
     *
     * @return string
     */
    public function getGridUrl() {
        return $this->getUrl('mana_core/productChooser', array(
            'products_grid' => true,
            '_current' => true,
            'uniq_id' => $this->getId(),
            'use_massaction' => $this->getUseMassaction(),
            'product_type_id' => $this->getProductTypeId(),
            'hidden_products' => $this->getHiddenProducts(),
        ));
    }

    /**
     * Getter
     *
     * @return array
     */
    public function getSelectedProducts() {
        parent::getSelectedProducts();
        if ($selectedProducts = $this->getRequest()->getParam('selected_products_comma_separated', null)) {
            $selectedProducts = explode(',', $selectedProducts);
            $this->setSelectedProducts($selectedProducts);
        }

        return $this->_selectedProducts;
    }

    public function getHiddenProducts() {
        if (!($result = parent::getHiddenProducts())) {
            $result = $this->getRequest()->getParam('hidden_products');
        }

        return $result;
    }

    protected function _basePrepareCollection() {
        if ($this->getCollection()) {

            $this->_preparePage();

            $columnId = $this->getParam($this->getVarNameSort(), $this->_defaultSort);
            $dir = $this->getParam($this->getVarNameDir(), $this->_defaultDir);
            $filter = $this->getParam($this->getVarNameFilter(), null);

            if (is_null($filter)) {
                $filter = $this->_defaultFilter;
            }

            if (is_string($filter)) {
                $data = $this->_backendHelper->prepareFilterString($filter);
                $this->_setFilterValues($data);
            } else {
                if ($filter && is_array($filter)) {
                    $this->_setFilterValues($filter);
                } else {
                    if (0 !== sizeof($this->_defaultFilter)) {
                        $this->_setFilterValues($this->_defaultFilter);
                    }
                }
            }

            if (isset($this->_columns[$columnId]) && $this->_columns[$columnId]->getIndex()) {
                $dir = (strtolower($dir) == 'desc') ? 'desc' : 'asc';
                $this->_columns[$columnId]->setDir($dir);
                $column = $this->_columns[$columnId]->getFilterIndex() ?
                    $this->_columns[$columnId]->getFilterIndex() : $this->_columns[$columnId]->getIndex();
                $this->getCollection()->setOrder($column, $dir);
            }

            if (!$this->_isExport) {
                $this->getCollection()->load();
                $this->_afterLoadCollection();
            }
        }

        return $this;
    }

}