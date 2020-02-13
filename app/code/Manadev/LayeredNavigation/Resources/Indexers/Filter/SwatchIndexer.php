<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation\Resources\Indexers\Filter;

use Zend_Db_Expr;
use Magento\Swatches\Model\Swatch;

class SwatchIndexer extends AttributeIndexer
{
    public function index($changes = ['all']) {
        $db = $this->getConnection();

        $fields = [
            'attribute_id' => new Zend_Db_Expr("`a`.`attribute_id`"),
            'unique_key' => new Zend_Db_Expr("CONCAT('attribute-', `a`.`attribute_id`)"),
            'additional_data' => new Zend_Db_Expr("`ca`.`additional_data`"),
        ];

        $select = $this->select($fields);

        $select->where("`ca`.`additional_data` IS NOT NULL");

        if ($whereClause = $this->scope->limitAttributeIndexing($changes, $fields)) {
            $select->where($whereClause);
        }

        foreach ($db->fetchAll($select) as $attribute) {
            if (!empty($attribute['additional_data'])) {
                $additionalData = $this->unserialize($attribute['additional_data']);
                if (is_array($additionalData) && isset($additionalData[Swatch::SWATCH_INPUT_TYPE_KEY])) {
                    $fields = [
                        'unique_key' => new Zend_Db_Expr("'{$attribute['unique_key']}'"),
                        'type' => new Zend_Db_Expr("'swatch'"),
                        'template' => new Zend_Db_Expr($db->quoteInto("COALESCE(`fge`.`template`, ?)",
                            $this->configuration->getDefaultSwatchTemplate())),
                        'swatch_input_type' => new Zend_Db_Expr("'{$additionalData[Swatch::SWATCH_INPUT_TYPE_KEY]}'"),
                    ];

                    $select = $this->select($fields);
                    $select->where("`a`.`attribute_id` = ?", $attribute['attribute_id']);

                    if (empty($changes['load_defaults'])) {
                        // convert SELECT into UPDATE which acts as INSERT on DUPLICATE unique keys
                        $sql = $select->insertFromSelect($this->getMainTable(), array_keys($fields));
    
                        // run the statement
                        $db->query($sql);
                    }
                    else {
                        return $select;
                    }
                }
            }

        }
        
        return null;
    }

    protected function select($fields) {
        return parent::select($fields)->where("`a`.`backend_type` IN('int', 'varchar')");
    }

    /**
     * Fix for Magento 2.2
     *
     * @param $data
     * @return mixed
     */
    protected function unserialize($data) {
        return substr($data, 0, 1) == '{' ? json_decode($data, true) : unserialize($data);
    }
}