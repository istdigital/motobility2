<?php
/**
 * NOTICE OF LICENSE
 * This source file is subject to the General Public License (GPL 2.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/GPL-2.0
 *
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade the module to newer
 * versions in the future.
 *
 * @copyright 2002-2016 TemplateMonster
 * @license http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
 */

namespace TemplateMonster\CountdownTimer\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;

use TemplateMonster\CountdownTimer\Block;
use TemplateMonster\CountdownTimer\Model;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CONFIG_PATH_ACTIVE = 'tm_blog/general/active';

    protected $_scopeConfig;

    protected $_timerBlock;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Block\Timer $timer
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_timerBlock = $timer;
    }

    public function getTimerHtml($product, $area) {
        $this->_timerBlock->setProduct($product);
        $this->_timerBlock->setConfigArea($area);
        $this->_timerBlock->setHelper($this);
        return $this->_timerBlock->toHtml();
    }

    public function isEnabled($area)
    {
        $path = "countdown_timer/general/" . $this->getGroupByArea($area) . "/enabled";
        return $this->getConfigValue($path);
    }

    public function getFormat($area)
    {
        $path = "countdown_timer/general/" . $this->getGroupByArea($area) . "/format";
        return $this->getConfigValue($path);
        /*switch ($config) {
            case 0 :
                return "%D days %H";
                break;
            case 1 :
                return "%D days %H:%M";
                break;
            case 2 :
                return "%D days %H:%M:%S";
                break;
            case 3 :
                return "%H:%M:%S";
                break;
        }*/
    }

    public function getSelector($area)
    {
        $path = "countdown_timer/general/" . $this->getGroupByArea($area) . "/selector";
        return $this->getConfigValue($path);
    }

    public function getGroupByArea($area)
    {
        switch ($area) {
            case Model\Timer::CATALOG_VIEW :
                return "catalog_page";
                break;
            case Model\Timer::PRODUCT_PAGE :
                return "product_page";
                break;
            case Model\Timer::WIDGET_LIST :
                return "widget";
                break;
        }
    }

    protected function getConfigValue($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }



}