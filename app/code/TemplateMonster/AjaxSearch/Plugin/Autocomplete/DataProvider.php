<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\AjaxSearch\Plugin\Autocomplete;

use Magento\CatalogSearch\Model\Autocomplete\DataProvider as DataProviderAutocomplete;
use TemplateMonster\AjaxSearch\Helper\AjaxSearch;

class DataProvider
{

    protected $_helper;

    public function __construct(AjaxSearch $helper)
    {
        $this->_helper = $helper;
    }

    public function aroundGetItems(DataProviderAutocomplete $subject, \Closure $proceed)
    {
        $autoCompleteSearchStatus = $this->_helper->getDefaultSearchStatus();
        $defaultSearchNumResult = $this->_helper->getDefaultSearchNumResult();

        if (!$autoCompleteSearchStatus || ($defaultSearchNumResult <= 0)) {
            return [];
        }
        $returnValue = $proceed();

        if ($returnValue && is_array($returnValue)) {
            $returnValue = array_slice($returnValue, 0, $defaultSearchNumResult);
        }

        return $returnValue;
    }
}
