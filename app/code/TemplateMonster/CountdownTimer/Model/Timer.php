<?php
/**
 * NOTICE OF LICENSE
 * This source file is subject to the General Public License (GPL 2.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/GPL-2.0
 *
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade the module to newer
 * versions in the future.
 *
 * @copyright 2002-2016 TemplateMonster
 * @license http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
 */

namespace TemplateMonster\CountdownTimer\Model;

use Magento\Framework\Model\AbstractModel;

class Timer extends AbstractModel
{
    const PRODUCT_PAGE = 0;
    const CATALOG_VIEW = 1;
    const WIDGET_LIST = 2;
}