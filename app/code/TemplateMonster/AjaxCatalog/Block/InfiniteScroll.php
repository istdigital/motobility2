<?php
namespace TemplateMonster\AjaxCatalog\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Block\Product\ProductList\Toolbar;

class InfiniteScroll extends Template
{
    protected $_template = 'TemplateMonster_AjaxCatalog::infinite_scroll.phtml';

    protected $_request;

    public function __construct(
        Context $context,
        CategoryRepository $categoryRepository,
        Toolbar $toolbar,
        array $data = []
    ){
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_request = $context->getRequest();
        $this->_toolbar = $toolbar;
        $this->categoryRepository = $categoryRepository;

        parent::__construct($context, $data);
    }

    public function getTotalNum()
    {
        return $this->_toolbar->getTotalNum();
    }

    /**
     * Number of products in category
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getProductsCount()
    {
        $block = $this->getLayout()->getBlock('category.products.list');

        if (!$block) {
            $block = $this->getLayout()->getBlock('search_result_list');
        }

        if (!$block) {
            return false;
        }

        $productCollection = $block->getLoadedProductCollection();

        return $productCollection->getSize();
    }

    /**
     * Get number of pagination pages
     *
     * @return mixed
     */
    public function getPagesToShow()
    {
        return min($this->getPagesCount(), $this->_pagesToShowOption());
    }

    /**
     * Get pagination pages from collection
     *
     * @return float
     */
    public function getPagesCount()
    {
        $pages = ceil($this->_getProductsCount()/$this->_productsPerPage());
        return $pages;
    }

    /**
     * If is enabled
     *
     * @return mixed
     */
    public function isEnabled()
    {
        return $this->_scopeConfig->getValue('ajaxcatalog/general/ajaxcatalog_infinite_scroll',
        \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get pages to show option
     */
    protected function _pagesToShowOption()
    {
        return $this->_scopeConfig->getValue('ajaxcatalog/general/ajaxcatalog_infinite_pages',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get number of products displayed in grid/list mode
     *
     * @return mixed
     */
    protected function _productsPerPage()
    {
        if($this->_request->getParam('product_list_mode') == 'list'){
            return $this->_scopeConfig->getValue('catalog/frontend/list_per_page',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        } else {
            return $this->_scopeConfig->getValue('catalog/frontend/grid_per_page',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
    }
}