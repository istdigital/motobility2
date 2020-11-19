<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */


namespace Amasty\Preorder\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * @var Operation\AddPreorderNote
     */
    private $addPreorderNote;

    /**
     * @var Operation\MoveTable
     */
    private $moveTable;

    public function __construct(
        Operation\AddPreorderNote $addPreorderNote,
        Operation\MoveTable $moveTable
    ) {
        $this->addPreorderNote = $addPreorderNote;
        $this->moveTable = $moveTable;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.3.0', '<')) {
            $this->addPreorderNote->execute($setup);
        }
        if (version_compare($context->getVersion(), '1.3.14', '<')) {
            $this->moveTable->execute($setup);
        }

        $setup->endSetup();
    }
}
