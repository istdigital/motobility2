<?php
/**
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace TemplateMonster\AjaxCatalog\Helper\Catalog\View;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Json\Helper\Data;

class ContentAjaxResponse extends AbstractHelper
{
    /**
     * @var ObjectManagerInterface
     */

    /**
     * @var PageFactory
     */
    protected $_pageFactory;

    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * ContentAjaxResponse constructor.
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param Data $helperData
     */
    public function __construct(Context $context,
                                PageFactory $pageFactory,
                                Data $helperData

    )
    {
        $this->_pageFactory = $pageFactory;
        $this->_helperData = $helperData;
        $this->_scopeConfig = $context->getScopeConfig();
        parent::__construct($context);
    }

    /**
     * Render part of page for Ajax Catalog request.
     *
     * @param $subject
     *
     * @return mixed
     */
    public function getAjaxSearchResult($subject, $proceed)
    {
        $response = $subject->getResponse();
        $page = $this->_pageFactory->create();
        $proceed();

        $result = [];

        try {
            $result['content'] = $page->getLayout()->renderElement('content');
            $result['layer'] = $page->getLayout()->renderElement('sidebar.main');
        } catch (\Exception $e) {
            $result['error'] = true;
            $result['message'] = 'Can not finished request!'.$e->getMessage();
        }

        return $response->representJson(
            $this->_helperData->jsonEncode($result)
        );
    }

    /**
     * Render part of page for Ajax Catalog request.
     *
     * @param $subject
     * @param $proceed
     *
     * @return mixed
     */
    public function getAjaxContent($subject, $proceed)
    {
        $response = $subject->getResponse();
        $page = $proceed();

        $result = [];

        try {
            $result['content'] = $page->getLayout()->renderElement('content');
            $result['layer'] = $page->getLayout()->renderElement('main.mana.layered_nav');
        } catch (\Exception $e) {
            $result['error'] = true;
            $result['message'] = 'Can not finished request'.$e->getMessage();
        }

        return $response->representJson(
            $this->_helperData->jsonEncode($result)
        );
    }

    /**
     * Rewrite options for productListToolbarForm widget.
     *
     * @param $result
     *
     * @return string
     */
    public function addActiveAjaxFilter($result)
    {
        $filtersArr = [];
        $config = $this->_scopeConfig;
        $showNumber = $config->getValue('ajaxcatalog/general/ajaxcatalog_shownumber_active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $showPagination = $config->getValue('ajaxcatalog/general/ajaxcatalog_pagination_active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $sortBy = $config->getValue('ajaxcatalog/general/ajaxcatalog_sortby_active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $listOrder = $config->getValue('ajaxcatalog/general/ajaxcatalog_listorder_active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $viewMode = $config->getValue('ajaxcatalog/general/ajaxcatalog_viewmode_active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $layer = $config->getValue('ajaxcatalog/general/ajaxcatalog_layer_active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        //product_list_order
        $filtersArr['product_list_limit'] = $showNumber;
        $filtersArr['product_list_dir'] = $sortBy;
        $filtersArr['product_list_order'] =  $listOrder;
        $filtersArr['post_list_dir'] = $sortBy;
        $filtersArr['post_list_order'] =  $listOrder;
        $filtersArr['product_list_mode'] = $viewMode;
        $filtersArr['showpagination'] = $showPagination;
        $filtersArr['layer'] = $layer;

        $filteredResult = array_filter($filtersArr, function ($var) {
            return $var != 0;
        });

        if (!$filteredResult) {
            return $result;
        }

        $options = json_decode($result, true);

        if (!array_key_exists('productListToolbarForm', $options)) {
            return $result;
        }

        $productListToolbarForm = $options['productListToolbarForm'];
        $productListToolbarForm['activeFilters'] = $filteredResult;

        return json_encode(['productListToolbarForm' => $productListToolbarForm]);
    }

    public function getMultiFilterAttributes(){
        $attrArr = [];
        $config = $this->_scopeConfig;
        $attr = $config->getValue('ajaxcatalog/general/ajaxcatalog_attribute_multy', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if($attr) {
            $attrArr = explode(",",$attr);
        }
        return $attrArr;
    }

}
