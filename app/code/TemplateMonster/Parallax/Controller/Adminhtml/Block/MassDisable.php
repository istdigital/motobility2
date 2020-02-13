<?php

namespace TemplateMonster\Parallax\Controller\Adminhtml\Block;

use TemplateMonster\Parallax\Api\Data\BlockInterface;

/**
 * Block disable mass-action.
 *
 * @package TemplateMonster\Parallax\Controller\Adminhtml\Block
 */
class MassDisable extends MassStatus
{
    /**
     * Parallax block status.
     *
     * @var int
     */
    protected $_status = BlockInterface::STATUS_DISABLED;

    /**
     * Action success message.
     *
     * @var string
     */
    protected $_successMessage = 'A total of %1 record(s) have been disabled.';
}
