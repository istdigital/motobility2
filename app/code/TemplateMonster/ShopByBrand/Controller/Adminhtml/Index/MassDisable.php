<?php

namespace TemplateMonster\ShopByBrand\Controller\Adminhtml\Index;

use TemplateMonster\ShopByBrand\Api\Data\BrandInterface;

/**
 * Mass disable action.
 *
 * @package TemplateMonster\ShopByBrand\Controller\Adminhtml\Action
 */
class MassDisable extends MassStatus
{
    /**
     * Brand status.
     *
     * @var int
     */
    protected $_status = BrandInterface::STATUS_DISABLED;

    /**
     * Action success message.
     *
     * @var string
     */
    protected $_successMessage = 'A total of %1 record(s) have been disabled.';
}
