<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
namespace Manadev\Core\Blocks\Adminhtml\ExtensionControl\Feature;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\Text;
use Magento\Framework\DataObject;

class ExtensionNameColumn extends Text
{
    public function render(DataObject $row) {
        $html = parent::render($row);
        if(!$row->getData('is_extension')) {
            $html = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$html;
        }

        return $html;
    }
}