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

namespace TemplateMonster\CountdownTimer\Block;

use Magento\Framework\View\Element\Template;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class Timer extends Template
{
    protected $_template = 'TemplateMonster_CountdownTimer::timer.phtml';

    protected $_helper;

    protected $_configArea = 0;

    protected $_product;

    private $_specialToDate;

    protected $_conf;

    public $isConfigurable = false;

    public function __construct(
        Configurable $conf,
        Template\Context $context,
        array $data = []
    ) {
        $this->_conf = $conf;
        parent::__construct($context, $data);
    }

    public function setHelper($helper)
    {
        $this->_helper = $helper;
    }

    public function setConfigArea($area)
    {
        $this->_configArea = $area;
    }

    public function getConfigArea()
    {
        return $this->_configArea;
    }

    public function setProduct($product)
    {
        $this->_product = $product;
    }

    public function getProduct()
    {
        return $this->_product;
    }

    public function toHtml()
    {
        //todo move to constructor or model
        $this->_specialToDate = 0;
        $this->isConfigurable = false;
        $this->initSpecialDate();
        return parent::toHtml();
    }

    public function getFormat()
    {
        return $this->_helper->getFormat($this->_configArea);
    }

    private function initSpecialDate()
    {
        $product = $this->getProduct();
        switch ($product->getTypeId()) {
            case 'configurable':
                $this->isConfigurable = true;
                $simples = $this->_conf->getUsedProducts($product);
                $date = 0;
                foreach ($simples as $simple) {
                    $spToDate = $simple->getSpecialToDate();
                    $spFromDate = $simple->getSpecialFromDate();
                    if ($spToDate && (!$spFromDate || (strtotime($spFromDate) < time()))) {
                        if ($date) {
                            if (strtotime($date) < strtotime($spToDate)) {
                                $date = $spToDate;
                            }
                        } else {
                            $date = $spToDate;
                        }
                    }
                }
                $this->_specialToDate = $date;
                break;
            default:
                $spToDate = $product->getSpecialToDate();
                $spFromDate = $product->getSpecialFromDate();
                if ($spToDate && (!$spFromDate || (strtotime($spFromDate) < time()))) {
                    $this->_specialToDate = $product->getSpecialToDate();
                }
        }
    }

    public function getSpecialToDate()
    {
        return $this->_specialToDate;
    }

    private function getEndDateTimestamp()
    {
        $date = $this->getSpecialToDate();
        $timestamp = strtotime($date);
        return strtotime("+1 day", $timestamp);
    }

    public function getEndDate()
    {
        return date('Y/m/d H:i:s', $this->getEndDateTimestamp());
    }

    public function getSelector()
    {
        return $this->_helper->getSelector($this->_configArea);
    }
    public function isAvailable()
    {
        if ($this->_product) {
            if ($this->_helper->isEnabled($this->_configArea)) {
                if ($this->getSpecialToDate()) {
                    if ($this->getEndDateTimestamp() > time()) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
}