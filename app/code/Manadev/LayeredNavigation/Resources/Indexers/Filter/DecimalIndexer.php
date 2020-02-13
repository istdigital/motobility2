<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation\Resources\Indexers\Filter;

use Zend_Db_Expr;

class DecimalIndexer extends AttributeIndexer
{
    protected function getIndexedFields($changes) {
        $db = $this->getConnection();

        if (empty($changes['load_defaults'])) {
            return array_merge(parent::getIndexedFields($changes), [
                'type' => new Zend_Db_Expr("'decimal'"),
                'template' => new Zend_Db_Expr($db->quoteInto("COALESCE(`fge`.`template`, ?)",
                    $this->configuration->getDefaultDecimalTemplate())),
            ]);
        }
        else {
            return array_merge(parent::getIndexedFields($changes), [
                'type' => new Zend_Db_Expr("'decimal'"),
                'template' => new Zend_Db_Expr($db->quoteInto("?", $this->configuration->getDefaultDecimalTemplate())),
            ]);
        }
    }

    protected function select($fields) {
        return parent::select($fields)->where("`a`.`backend_type` = 'decimal' AND `a`.`attribute_code` <> 'price'");
    }

}