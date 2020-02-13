<?php

/**
 *
 * Copyright © 2016 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\GoogleMap\Helper\Config;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface as Scope;

class Settings extends AbstractHelper
{

    const CONFIG_SECTION = 'google_map';

    protected $_groups = [
        'home',
        'contacts',
        'footer'
    ];

    protected $_optionsSettings = [
        'width',
        'height',
        'show_on',
        'selector'
    ];

    protected $_scoreConfig;

    public function __construct(Context $context)
    {
        $this->_scoreConfig = $context->getScopeConfig();
        parent::__construct($context);
    }


    protected function _getConfigData($group,$item)
    {
        $path = static::CONFIG_SECTION. '/' . $group . '/' . $item;
        return $this->_scoreConfig->getValue($path, Scope::SCOPE_STORE);
    }

    protected function _getConfigDataGroup($group) {
        $resultArr = [];
        foreach($this->_optionsSettings as $item) {
            $resultArr[$item] = $this->_getConfigData($group,$item);
        }
        return $resultArr;
    }

    public function getActiveGroup()
    {
        $activePageArray = [];
        foreach($this->_groups as $group) {
            if($this->_getConfigData($group,'active')) {
                $activePageArray[] = $group;
            }
        }
        return $activePageArray;
    }

    public function getPagesSettings(){
        $settingsArr = [];
        foreach($this->getActiveGroup() as $group) {
            $settingsArr[$group] = $this->_getConfigDataGroup($group);
        }
        $settingsArr['api'] = $this->_getConfigData('general','api_key');
        return $settingsArr;
    }
}