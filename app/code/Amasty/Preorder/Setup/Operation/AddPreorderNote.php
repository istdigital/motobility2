<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */


namespace Amasty\Preorder\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class AddPreorderNote
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_preorder_order_item_preorder'),
            'preorder_note',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => false,
                'default' => false,
                'comment' => 'Preorder Note'
            ]
        );
    }
}
