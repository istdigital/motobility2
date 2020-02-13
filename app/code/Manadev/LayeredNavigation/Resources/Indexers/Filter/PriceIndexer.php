<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation\Resources\Indexers\Filter;

use Zend_Db_Expr;

class PriceIndexer extends AttributeIndexer
{
    protected function getIndexedFields($changes) {
        $db = $this->getConnection();

        if (empty($changes['load_defaults'])) {
            return array_merge(parent::getIndexedFields($changes), [
                'type' => new Zend_Db_Expr("'price'"),
                'template' => new Zend_Db_Expr($db->quoteInto("COALESCE(`fge`.`template`, ?)",
                    $this->configuration->getDefaultPriceTemplate())),
            ]);
        }
        else {
            return array_merge(parent::getIndexedFields($changes), [
                'type' => new Zend_Db_Expr("'price'"),
                'template' => new Zend_Db_Expr($db->quoteInto("?", $this->configuration->getDefaultPriceTemplate())),
            ]);
        }
    }

    protected function select($fields) {
        return parent::select($fields)->where("`a`.`attribute_code` = 'price'");
    }
}