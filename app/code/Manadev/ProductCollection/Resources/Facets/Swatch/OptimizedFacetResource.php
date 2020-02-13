<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\ProductCollection\Resources\Facets\Swatch;

use Magento\Framework\DB\Select;
use Manadev\ProductCollection\Contracts\Facet;
use Manadev\ProductCollection\Resources\Facets\Dropdown\OptimizedFacetResource as DropdownOptimizedFacetResource;
use Zend_Db_Expr;

class OptimizedFacetResource extends DropdownOptimizedFacetResource
{
    public function getFields(Facet $facet) {
        return array_merge(parent::getFields($facet), [
            'swatch_type' => new Zend_Db_Expr("COALESCE(`ss`.`type`, `sg`.`type`)"),
            'swatch' => new Zend_Db_Expr("COALESCE(`ss`.`value`, `sg`.`value`)"),
        ]);
    }

    public function addJoins(Select $select, Facet $facet) {
        parent::addJoins($select, $facet);
        $db = $this->getConnection();

        $select
            ->joinLeft(['sg' => $this->getTable('eav_attribute_option_swatch')],
                $db->quoteInto("`sg`.`option_id` = `eav`.`value` AND `sg`.`store_id` = ?", 0), null)
            ->joinLeft(['ss' => $this->getTable('eav_attribute_option_swatch')],
                $db->quoteInto("`ss`.`option_id` = `eav`.`value` AND `ss`.`store_id` = ?", $this->getStoreId()), null);
    }

    public function getBatchType($facet) {
        return null;
    }
}