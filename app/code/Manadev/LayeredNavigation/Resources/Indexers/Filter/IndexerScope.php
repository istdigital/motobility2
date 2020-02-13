<?php
/** 
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation\Resources\Indexers\Filter;

use Manadev\Core\Exceptions\NotImplemented;

class IndexerScope {
    /**
     * Returns SQL condition limiting records which are marked for deletion and
     * then deleted if not indexed or empty string if all records should be
     * processed.
     * @param array $changes indicates which exactly changes happened in the
     *                       system
     * @return string
     * @throws NotImplemented
     */
    public function limitMarkingForDeletion($changes) {
        $storeIdExpr = '`store_id`';
        $attributeIdExpr = '`attribute_id`';
        $filterIdExpr = '`filter_id`';

        $condition = "$storeIdExpr = 0";

        if (isset($changes['attributes'])) {
            $condition .= " AND $attributeIdExpr IN (" . implode(', ', $changes['attributes']) . ")";
        }
        elseif (isset($changes['filters'])) {
            // nothing to mark
            $condition .= " AND (1 <> 1)";
        }
        elseif (in_array('all', $changes)) {
            // nothing to limit
        }
        else {
            throw new NotImplemented();
        }

        return $condition;
    }

    /**
     * Returns SQL condition limiting records which are marked for deletion and
     * then deleted if not indexed or empty string if all records should be
     * processed.
     * @param array $changes indicates which exactly changes happened in the
     *                       system
     * @return string
     * @throws NotImplemented
     */
    public function limitDeletion($changes) {
        $storeIdExpr = '`store_id`';
        $attributeIdExpr = '`attribute_id`';
        $filterIdExpr = '`filter_id`';

        $condition = "`is_deleted` = 1 AND $storeIdExpr = 0";

        if (isset($changes['attributes'])) {
            $condition .= " AND $attributeIdExpr IN (" . implode(', ', $changes['attributes']) . ")";
        }
        elseif (isset($changes['filters'])) {
            // nothing to delete
            $condition .= " AND (1 <> 1)";
        }
        elseif (in_array('all', $changes)) {
            // nothing to limit
        }
        else {
            throw new NotImplemented();
        }

        return $condition;
    }

    /**
     * Returns SQL condition limiting records to which global ID should be
     * assigned or empty string if all records should be processed.
     * @param array $changes indicates which exactly changes happened in the
     *                       system
     * @return string
     * @throws NotImplemented
     */
    public function limitIdAssignment($changes) {
        $storeIdExpr = '`store_id`';
        $attributeIdExpr = '`attribute_id`';
        $filterIdExpr = '`filter_id`';

        $condition = "`filter_id` IS NULL";
        $condition .= " AND $storeIdExpr = 0";

        if (isset($changes['attributes'])) {
            $condition .= " AND $attributeIdExpr IN (" . implode(', ', $changes['attributes']) . ")";
        }
        elseif (isset($changes['filters'])) {
            $condition .= " AND $filterIdExpr IN (" . implode(', ', array_keys($changes['filters'])) . ")";
        }
        elseif (in_array('all', $changes)) {
            // nothing to limit
        }
        else {
            throw new NotImplemented();
        }

        return $condition;
    }

    /**
     * Returns SQL condition limiting which attribute based filters should be
     * indexed or empty string if all records should be processed.
     * @param array $changes indicates which exactly changes happened in the
     *                       system
     * @param $fields
     * @return string
     * @throws NotImplemented
     */
    public function limitAttributeIndexing($changes, $fields) {
        $attributeIdExpr = '`a`.`attribute_id`';
        $filterIdExpr = '`fge`.`filter_id`';

        $condition = "";

        if (isset($changes['attributes'])) {
            $condition = "$attributeIdExpr IN (" . implode(', ', $changes['attributes']) . ")";
        }
        elseif (isset($changes['filters'])) {
            $condition = "{$fields['unique_key']} IN (" . implode(', ', array_values($changes['filters'])) . ")";
        }
        elseif (in_array('all', $changes)) {
            // nothing to limit
        }
        else {
            throw new NotImplemented();
        }

        return $condition;
    }

    /**
     * Returns SQL condition preventing category filter indexing or empty
     * string if category filter should be indexed
     * @param array $changes indicates which exactly changes happened in the
     *                       system
     * @param $fields
     * @return string
     * @throws NotImplemented
     */
    public function limitCategoryIndexing($changes, $fields) {
        $filterIdExpr = '`fge`.`filter_id`';

        $condition = "";

        if (isset($changes['attributes'])) {
            $condition = "1 <> 1";
        }
        elseif (isset($changes['filters'])) {
            $condition = "{$fields['unique_key']} IN (" . implode(', ', array_values($changes['filters'])) . ")";
        }
        elseif (in_array('all', $changes)) {
            // nothing to limit
        }
        else {
            throw new NotImplemented();
        }

        return $condition;
    }

    /**
     * Returns SQL condition limiting which store level filters should be
     * indexed or empty string if all records should be processed.
     * @param array $changes indicates which exactly changes happened in the
     *                       system
     * @param $fields
     * @return string
     * @throws NotImplemented
     */
    public function limitStoreLevelIndexing($changes, $fields) {
        $attributeIdExpr = "`fg`.`attribute_id`";
        $filterIdExpr = '`fg`.`filter_id`';

        $condition = "`fg`.`store_id` = 0";

        if (isset($changes['attributes'])) {
            $condition .= " AND $attributeIdExpr IN (" . implode(', ', $changes['attributes']) . ")";
        }
        elseif (isset($changes['filters'])) {
            $condition .= " AND $filterIdExpr IN (" . implode(', ', array_keys($changes['filters'])) . ")";
        }
        elseif (in_array('all', $changes)) {
            // nothing to limit
        }
        else {
            throw new NotImplemented();
        }

        return $condition;
    }
}