<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\ProductCollection\Resources\Facets\Price;

use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\Interval;
use Magento\Framework\Search\Dynamic\Algorithm;
use Magento\Framework\Search\Dynamic\IntervalFactory;
use Magento\Store\Model\StoreManagerInterface;
use Manadev\Core\Traits\FrontendDependencies;
use Manadev\ProductCollection\Contracts\Facet;
use Manadev\ProductCollection\Facets\Price\EqualizedCountFacet;
use Manadev\ProductCollection\Resources\HelperResource;
use Zend_Db_Expr;
use Magento\Framework\Model\ResourceModel\Db;
use Manadev\ProductCollection\Factory;
use Manadev\ProductCollection\Configuration;

class EqualizedCountFacetResource extends BaseFacetResource
{
    use FrontendDependencies;

    /**
     * @var Algorithm
     */
    protected $algorithm;
    /**
     * @var IntervalFactory
     */
    protected $intervalFactory;

    public function __construct(Db\Context $context, Factory $factory,
        StoreManagerInterface $storeManager, Configuration $configuration, HelperResource $helperResource,
        Algorithm $algorithm, IntervalFactory $intervalFactory, $resourcePrefix = null)
    {
        parent::__construct($context, $factory, $storeManager, $configuration, $helperResource, $resourcePrefix);
        $this->algorithm = $algorithm;
        $this->intervalFactory = $intervalFactory;
    }

    public function isPreparationStepNeeded() {
        return true;
    }

    public function getPreparationFilterCallback(Facet $facet) {
        return $this->helperResource->dontApplyLayeredNavigationFilters();
    }

    public function prepare(Select $select, Facet $facet) {
        $facet->setPreparationSelect(clone $select);
        /* @var $facet EqualizedCountFacet */
        $db = $this->getConnection();

        $stats = $db->fetchRow($this->statSelect($select));

        $this->algorithm->setStatistics($stats['min'], $stats['max'], $stats['standard_deviation'], $stats['count']);
        $this->algorithm->setLimits($stats['min'], $stats['max'] + 0.01);

        $interval = $this->getInterval($select);
        $facet->setRanges($this->algorithm->calculateSeparators($interval));
    }

    /**
     * @param Select $select
     * @param Facet $facet
     * @return mixed
     */
    public function count(Select $select, Facet $facet) {
        /* @var $facet EqualizedCountFacet */
        $db = $this->getConnection();

        $counts = $facet->getRanges();

        // TODO: continue here
        if (!$this->areSelectsEqual($select, $facet->getPreparationSelect())) {
            $secondCounts = $db->fetchPairs($this->secondCountSelect($select, $counts));
            foreach ($counts as $index => &$item) {
                if (isset($secondCounts[$index])) {
                    $item['count'] = $secondCounts[$index];
                }
            }

        }

        $minimumOptionCount = $facet->getHideWithSingleVisibleItem() ? 2 : 1;
        if (count($counts) < $minimumOptionCount) {
            return false;
        }

        $count = count($counts);

        $counts[0]['from'] = ''; // We should not calculate min and max value
        $counts[$count - 1]['to'] = '';
        for ($key = 0; $key < $count; $key++) {
            if (isset($counts[$key + 1])) {
                $counts[$key]['to'] = $counts[$key + 1]['from'];
            }
        }

        $appliedRanges = $this->implodeRanges($facet->getAppliedRanges());

        foreach ($counts as $index => &$item) {
            $from = $item['from'];
            $to = $item['to'];
            unset($item['from']);
            unset($item['to']);

            $this->helperResource->formatPriceRangeFacet($item, $from, $to, $index == 0, $index == $count - 1);
            $item['sort_order'] = $index;
            $item['is_selected'] = in_array($item['value'], $appliedRanges);
        }

        return $counts;
    }

    protected function getInterval(Select $select) {
        $this->helperResource->clearFacetSelect($select);
        $select->distinct(false);
        $select->columns(['value' => new Zend_Db_Expr($this->helperResource->getPriceExpression())]);

        return $this->getObjectManager()->create(Interval::class, ['select' => $select]);
    }

    protected function implodeRanges($ranges) {
        if ($ranges === false) {
            return false;
        }

        return array_map(function($range) { return implode('-', $range); }, $ranges);
    }

    protected static $selectParts = array(
        Select::COLUMNS,
        Select::UNION,
        Select::FROM,
        Select::WHERE,
        Select::GROUP,
        Select::HAVING,
        Select::ORDER,
        Select::LIMIT_COUNT,
        Select::LIMIT_OFFSET,
        Select::FOR_UPDATE,
    );

    protected function areSelectsEqual(Select $select1, Select $select2) {
        foreach (static::$selectParts as $part) {
            if ($select1->getPart($part) != $select2->getPart($part)) {
                return false;
            }
        }
        return true;
    }

    protected function secondCountSelect(Select $select, $counts) {
        $this->helperResource->clearFacetSelect($select);

        $count = count($counts);
        $rangeExpr = "0";
        if ($count >= 2) {
            $rangeExpr = "IF ({$this->helperResource->getPriceExpression()} < {$counts[0]['to']}, 0, ";
            for ($index = 1; $index < $count - 1; $index++) {
                $rangeExpr .= "IF ({$this->helperResource->getPriceExpression()} >= {$counts[$index]['from']} AND
                    {$this->helperResource->getPriceExpression()} < {$counts[$index]['to']}, $index, ";
            }
            $lastIndex = $count - 1;
            $rangeExpr .= "{$lastIndex}" . str_repeat(")", $count - 1);
        }

        $columns = [
            'range' => new Zend_Db_Expr("$rangeExpr"),
            'count' => new Zend_Db_Expr('COUNT(*)'),
        ];

        $select->columns($columns)->group($columns['range'])->order($columns['range']);

        return $select;
    }

}