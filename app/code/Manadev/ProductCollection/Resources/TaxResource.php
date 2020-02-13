<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\ProductCollection\Resources;

use Magento\Framework\Model\ResourceModel\Db;
use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Tax\Model\ClassModel;
use Magento\Tax\Model\Config;
use Manadev\ProductCollection\Configuration;

class TaxResource extends Db\AbstractDb
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var Calculation
     */
    protected $calculation;
    /**
     * @var TaxHelper
     */
    protected $taxHelper;
    /**
     * @var Configuration
     */
    protected $configuration;

    public function __construct(Db\Context $context, StoreManagerInterface $storeManager, Calculation $calculation,
        TaxHelper $taxHelper, Configuration $configuration, $resourcePrefix = null)
    {
        parent::__construct($context, $resourcePrefix);
        $this->storeManager = $storeManager;
        $this->calculation = $calculation;
        $this->taxHelper = $taxHelper;
        $this->configuration = $configuration;
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct() {
        $this->_setMainTable('tax_calculation');
    }

    public function applyTaxToPriceExpression($priceExpr) {
        if (!$this->configuration->calculateTaxInPriceFilter()) {
            return $priceExpr;
        }

        $storeId = $this->storeManager->getStore()->getId();
        $defaultTaxExpr = $this->getTaxExpr($this->calculation->getDefaultRateRequest($storeId));
        $currentTaxExpr = $this->getTaxExpr($this->calculation->getRateRequest(null, null, null, $storeId));

        $taxExpr = '';
        if ($this->taxHelper->priceIncludesTax($storeId)) {
            if ($defaultTaxExpr) {
                $taxExpr = "-({$priceExpr}/(1+({$defaultTaxExpr}))*{$defaultTaxExpr})";
            }
            if (!$this->taxHelper->getPriceDisplayType($storeId) == Config::DISPLAY_TYPE_EXCLUDING_TAX && $currentTaxExpr) {
                $taxExpr .= "+(({$priceExpr}{$taxExpr})*{$currentTaxExpr})";
            }
        } else {
            if ($this->taxHelper->getPriceDisplayType($storeId) == Config::DISPLAY_TYPE_INCLUDING_TAX) {
                if ($currentTaxExpr) {
                    $taxExpr .= "+({$priceExpr}*{$currentTaxExpr})";
                }
            }
        }
        return $priceExpr . $taxExpr;
    }

    protected function getTaxExpr(DataObject $request) {
        $rates = $this->getRates($request);
        if (!count($rates)) {
            return '';
        }

        $cases = '';
        foreach ($rates as $classId => $rate) {
            $cases .= sprintf("WHEN %d THEN %12.4f ", $classId, $rate / 100);
        }

        return "CASE `price_index`.`tax_class_id` {$cases} ELSE 0 END";
    }

    protected function getRates(DataObject $request) {
        $db = $this->getConnection();

        $select = $db->select()
            ->from(['c' => $this->getTable('tax_class')], ['class_id'])
            ->where("`c`.`class_type` = ?", ClassModel::TAX_CLASS_TYPE_PRODUCT);

        $result = [];
        foreach ($db->fetchCol($select) as $classId) {
            $request->setData('product_class_id', $classId);
            $result[$classId] = $this->calculation->getRate($request);
        }

        return $result;
    }


}