<?php

namespace TemplateMonster\Parallax\Controller\Adminhtml\BlockItem;

use TemplateMonster\Parallax\Api\Data\BlockItemInterface;

/**
 * Block item disable mass-action.
 *
 * @package TemplateMonster\Parallax\Controller\Adminhtml\BlockItem
 */
class MassDisable extends MassStatus
{
    /**
     * Parallax block item status.
     *
     * @var int
     */
    protected $_status = BlockItemInterface::STATUS_DISABLED;

    /**
     * Action success message.
     *
     * @var string
     */
    protected $_successMessage = 'A total of %1 record(s) have been disabled.';
}
