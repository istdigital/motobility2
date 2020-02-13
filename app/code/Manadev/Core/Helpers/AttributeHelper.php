<?php
/**
 * Created by PhpStorm.
 * User: Vernard
 * Date: 8/14/2015
 * Time: 9:56 PM
 */

namespace Manadev\Core\Helpers;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Manadev\Core\Data\AttributeData;

class AttributeHelper
{
    /**
     * @var AttributeData[]
     */
    protected $attributes = [];

    protected $entityTables = [
        'catalog_category' => 'catalog_category_entity'
    ];

    /**
     * @var Resource|Resource
     */
    private $resource;

    /**
     * @param ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource
    ) {

        $this->resource = $resource;
        $this->db = $resource->getConnection('default');
    }

    public function get($entityType, $attributeCode, $columns = []) {
        $key = $entityType . '-' . $attributeCode . '-' . implode('-', $columns);

        if (!isset($this->attributes[$key])) {
            $columns = array_merge($columns, ['attribute_id', 'backend_type', 'backend_table']);

            $attribute = new AttributeData($this->db->fetchRow(
                $this->db->select()
                    ->from(['a' => $this->resource->getTableName('eav_attribute')], $columns)
                    ->join(['t' => $this->resource->getTableName('eav_entity_type')],
                        't.entity_type_id = a.entity_type_id', null)
                    ->where('a.attribute_code = ?', $attributeCode)
                    ->where('t.entity_type_code = ?', $entityType)
            ));

            $baseTable = isset($this->entityTables[$entityType])
                ? $this->entityTables[$entityType]
                : $entityType . '_entity';

            $attribute->table = $attribute->backend_table
                ? $attribute->backend_table
                : $this->resource->getTableName($baseTable . '_' . $attribute->backend_type);

            $this->attributes[$key] = $attribute;
        }

        return $this->attributes[$key];
    }

    /**
     * @param Select $select
     * @param AttributeData $attribute
     * @param string $entityIdExpr
     * @param string|null $globalAlias
     * @param string|null $storeLevelAlias
     * @param string|null $storeIdExpr
     */
    public function join($select, $attribute, $entityIdExpr, $globalAlias = null, $storeLevelAlias = null,
        $storeIdExpr = null)
    {
        if ($globalAlias) {
            $select->joinLeft([$globalAlias => $attribute->table],
                "`$globalAlias`.`entity_id` = $entityIdExpr" .
                $this->db->quoteInto(" AND `$globalAlias`.`attribute_id` = ?", $attribute->attribute_id) .
                " AND `$globalAlias`.`store_id` = 0",
                null
            );
        }

        if ($storeLevelAlias) {
            $select->joinLeft([$storeLevelAlias => $attribute->table],
                "`$storeLevelAlias`.`entity_id` = $entityIdExpr" .
                $this->db->quoteInto(" AND `$storeLevelAlias`.`attribute_id` = ?", $attribute->attribute_id) .
                " AND `$storeLevelAlias`.`store_id` = $storeIdExpr",
                null
            );
        }
    }

    /**
     * @param Select $select
     * @param AttributeData $attribute
     * @param string $rowIdExpr
     * @param string|null $globalAlias
     * @param string|null $storeLevelAlias
     * @param string|null $storeIdExpr
     */
    public function joinStaged($select, $attribute, $rowIdExpr, $globalAlias = null,
        $storeLevelAlias = null, $storeIdExpr = null)
    {
        if ($globalAlias) {
            $select->joinLeft([$globalAlias => $attribute->table],
                "`$globalAlias`.`row_id` = $rowIdExpr" .
                $this->db->quoteInto(" AND `$globalAlias`.`attribute_id` = ?", $attribute->attribute_id) .
                " AND `$globalAlias`.`store_id` = 0",
                null
            );
        }

        if ($storeLevelAlias) {
            $select->joinLeft([$storeLevelAlias => $attribute->table],
                "`$storeLevelAlias`.`row_id` = $rowIdExpr" .
                $this->db->quoteInto(" AND `$storeLevelAlias`.`attribute_id` = ?", $attribute->attribute_id) .
                " AND `$storeLevelAlias`.`store_id` = $storeIdExpr",
                null
            );
        }
    }
}