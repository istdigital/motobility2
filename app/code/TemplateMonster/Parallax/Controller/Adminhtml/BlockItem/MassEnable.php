<?php

namespace TemplateMonster\Parallax\Controller\Adminhtml\BlockItem;

use TemplateMonster\Parallax\Api\Data\BlockItemInterface;

/**
 *  Block item enable mass-action.
 *
 * @package TemplateMonster\Parallax\Controller\Adminhtml\BlockItem
 */
class MassEnable extends MassStatus
{
    /**
     * Parallax block item status.
     *
     * @var int
     */
    protected $_status = BlockItemInterface::STATUS_ENABLED;

    /**
     * Action success message.
     *
     * @var string
     */
    protected $_successMessage = 'A total of %1 record(s) have been enabled.';
}
