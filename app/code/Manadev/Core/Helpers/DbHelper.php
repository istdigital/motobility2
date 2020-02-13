<?php
/** 
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core\Helpers;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Zend_Db_Expr;

class DbHelper {
    /**
     * @param AdapterInterface $db
     * @param $tableName
     * @param array $fields
     * @param bool $onDuplicate
     * @return string
     */
    public function insert($db, $tableName, $fields = [], $onDuplicate = true) {
        $sql = "INSERT INTO `{$tableName}` ";
        $sql .= "(`" . implode('`,`', array_keys($fields)) . "`) ";

        $values = array_map(function ($field) use ($db){
                return $field === null ? 'NULL' : $db->quoteInto("?", $field);
        }, $fields);
        $sql .= "VALUES (" . implode(',', $values) . ") ";

        if ($onDuplicate && $fields) {
            $sql .= " ON DUPLICATE KEY UPDATE";
            $updateFields = [];
            foreach ($fields as $key => $field) {
                $key = $db->quoteIdentifier($key);
                $updateFields[] = "{$key}=VALUES({$key})";
            }
            $sql .= " " . implode(', ', $updateFields);
        }

        return $sql;
    }

    public function wrapIntoZendDbExpr($fields) {
        $result = array();
        foreach ($fields as $key => $value) {
            $result[$key] = new Zend_Db_Expr($value);
        }
        return $result;
    }

    /**
     * @param AdapterInterface $db
     * @param Select $select
     * @param int $pageSize
     * @return \Generator
     */
    public function fetchAllPaged($db, $select, $pageSize = 1000) {
        $page = 1;
        $select = clone $select;
        do {
            $data = $db->fetchAll($select->limitPage($page++, $pageSize));
            foreach ($data as $item) {
                yield $item;
            }
        } while (count($data) == $pageSize);
    }
}