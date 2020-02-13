<?php

namespace Manadev\Core\Resources;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Model\ResourceModel\Db;

class TemporaryResource extends Db\AbstractDb
{
    protected function _construct() {
        $this->_setMainTable('catalog_product_entity');
    }

    /**
     * @param callable $callback
     * @return string
     * @throws \Zend_Db_Exception
     */
    public function createTable(callable $callback) {
        $db = $this->getConnection();
        $tableName = $this->generateTableName();

        $table = $db->newTable($tableName);
        $callback($table);

        $db->createTemporaryTable($table);
        //$db->createTable($table);
        return $tableName;
    }

    protected function generateTableName() {
        return 'temp_' . sha1(uniqid('', true));
    }

    /**
     * @return string
     * @throws \Zend_Db_Exception
     */
    public function createProductIdTable() {
        return $this->createTable(function(Table $table) {
            $db = $this->getConnection();

            $table
                ->addColumn('entity_id', Table::TYPE_INTEGER, null,
                    ['unsigned' => true, 'nullable' => false])
                ->addIndex($db->getIndexName($this->getTable($table->getName()), ['entity_id'], 'unique'),
                    ['entity_id'], ['type' => 'unique']);
        });
    }

    /**
     * @param callable $callback
     * @return string
     * @throws \Zend_Db_Exception
     */
    public function createOptionCountTable($callback) {
        return $this->createTable(function(Table $table) use ($callback) {
            $db = $this->getConnection();

            $table
                ->addColumn('value', Table::TYPE_INTEGER, null,
                    ['unsigned' => true, 'nullable' => false])
                ->addIndex($db->getIndexName($this->getTable($table->getName()), ['value'], 'unique'),
                    ['value'], ['type' => 'unique'])
                ->addColumn('count', Table::TYPE_INTEGER, null,
                    ['unsigned' => true, 'nullable' => false]);

            $callback($table);
        });
    }

    public function createSearchResultTable() {
        return $this->createTable(function(Table $table) {
            $table
                ->addColumn('entity_id' , Table::TYPE_INTEGER, 10,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true], 'Entity ID')
                ->addColumn('score', Table::TYPE_DECIMAL, [32, 16],
                    ['unsigned' => true, 'nullable' => false], 'Score');
        });
    }
}