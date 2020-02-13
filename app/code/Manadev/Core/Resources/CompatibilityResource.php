<?php

namespace Manadev\Core\Resources;

use Magento\Framework\Model\ResourceModel\Db;

class CompatibilityResource extends Db\AbstractDb
{

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct() {
        $this->_setMainTable('catalog_product_entity');
    }

    public function eavIndexSourceIdExists() {
        $db = $this->getConnection();

        return $db->tableColumnExists($this->getTable('catalog_product_index_eav'), 'source_id');
    }
}