<?php

namespace TemplateMonster\Parallax\Setup;

use TemplateMonster\Parallax\Api\Data\BlockInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema.
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();

        $table = $this->_buildParallaxBlockTable($installer);
        $installer->getConnection()->createTable($table);

        $table = $this->_buildParallaxBlockStoreTable($installer);
        $installer->getConnection()->createTable($table);

        $table = $this->_buildParallaxBlockItemTable($installer);
        $installer->getConnection()->createTable($table);
    }

    /**
     * @param $installer
     *
     * @return mixed
     */
    protected function _buildParallaxBlockTable($installer)
    {
        $table = $installer->getConnection()
            ->newTable($installer->getTable('parallax_block'))
            ->addColumn(
                BlockInterface::BLOCK_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Parallax Block Id'
            )
            ->addColumn(
                'name',
                Table::TYPE_TEXT,
                100,
                ['nullable' => false],
                'Name'
            )
            ->addColumn(
                'css_class',
                Table::TYPE_TEXT,
                100,
                ['nullable' => true],
                'CSS-class'
            )
            ->addColumn(
                'is_full_width',
                Table::TYPE_INTEGER,
                1,
                ['unsigned' => true, 'nullable' => false],
                'Full width'
            )
            ->addColumn(
                'status',
                Table::TYPE_SMALLINT,
                1,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Status'
            );

        return $table;
    }

    /**
     * @param $installer
     *
     * @return mixed
     */
    protected function _buildParallaxBlockStoreTable($installer)
    {
        $table = $installer->getConnection()
            ->newTable($installer->getTable('parallax_block_store'))
            ->addColumn(
                'block_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'unsigned' => true, 'primary' => true],
                'Parallax Block Id'
            )
            ->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Store Id'
            )
            ->addIndex(
                $installer->getIdxName('parallax_block_store', ['store_id']),
                ['store_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'parallax_block_store', 'block_id', 'parallax_block',
                    'block_id'
                ),
                'block_id',
                $installer->getTable('parallax_block'),
                'block_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'parallax_block_store', 'store_id', 'store', 'store_id'
                ),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Parallax Block To Store Linkage Table');

        return $table;
    }

    /**
     * @param $installer
     *
     * @return mixed
     */
    protected function _buildParallaxBlockItemTable($installer)
    {
        $table = $installer->getConnection()
            ->newTable($installer->getTable('parallax_block_item'))
            ->addColumn(
                'item_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Item Id'
            )
            ->addColumn(
                'block_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Block Id'
            )
            ->addColumn(
                'name',
                Table::TYPE_TEXT,
                100,
                ['nullable' => false],
                'Name'
            )
            ->addColumn(
                'type',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Item Type'
            )
            ->addColumn(
                'offset',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Offset'
            )
            ->addColumn(
                'is_inverse',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Is inverse'
            )
            ->addColumn(
                'css_class',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Status'
            )
            ->addColumn(
                'layout_speed',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Layout Speed'
            )
            ->addColumn(
                'sort_order',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Sort Order'
            )
            ->addColumn(
                'is_fade',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Is fade'
            )
            ->addColumn(
                'text',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Status'
            )
            ->addColumn(
                'image',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Image'
            )
            ->addColumn(
                'video_format',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Video Format'
            )
            ->addColumn(
                'video_id',
                Table::TYPE_TEXT,
                100,
                ['nullable' => true],
                'Video Id'
            )
            ->addColumn(
                'video_mp4',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Video Mp4 File'
            )
            ->addColumn(
                'video_webm',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Video WebM File'
            )
            ->addColumn(
                'status',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Status'
            );

        return $table;
    }
}
