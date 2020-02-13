<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\AjaxSearch\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface as Scope;

class AjaxSearch extends AbstractHelper
{

    protected $_scoreConfig;

    public function __construct(Context $context)
    {
        $this->_scoreConfig = $context->getScopeConfig();
        parent::__construct($context);
    }

    public function getDefaultSearchStatus()
    {
        $active = $this->_scoreConfig->getValue('ajaxsearch/ajaxsearch/ajaxsearch_default_active', Scope::SCOPE_STORE);
        return empty($active) ? false : true;
    }

    public function getCategorySearchStatus()
    {
        $active = $this->_scoreConfig->getValue('ajaxsearch/ajaxsearch/ajaxsearch_category_active', Scope::SCOPE_STORE);
        $flatActive = $this->_scoreConfig->getValue('catalog/frontend/flat_catalog_category', Scope::SCOPE_STORE);
        if (empty($active) || empty($flatActive)) {
            return false;
        }
        return true;
    }

    public function getProductSearchStatus()
    {
        $active = $this->_scoreConfig->getValue('ajaxsearch/ajaxsearch/ajaxsearch_product_active', Scope::SCOPE_STORE);
        $flatActive = $this->_scoreConfig->getValue('catalog/frontend/flat_catalog_product', Scope::SCOPE_STORE);
        if (empty($active) || empty($flatActive)) {
            return false;
        }
        return true;
    }

    public function getDefaultSearchNumResult()
    {
        $number = $this->_scoreConfig->getValue('ajaxsearch/ajaxsearch/ajaxsearch_default_number', Scope::SCOPE_STORE);
        return $number;
    }

    public function getCategorySearchNumResult()
    {
        $number = $this->_scoreConfig->getValue('ajaxsearch/ajaxsearch/ajaxsearch_category_number', Scope::SCOPE_STORE);
        return $number;
    }

    public function getProductSearchNumResult()
    {
        $number = $this->_scoreConfig->getValue('ajaxsearch/ajaxsearch/ajaxsearch_product_number', Scope::SCOPE_STORE);
        return $number;
    }
}
