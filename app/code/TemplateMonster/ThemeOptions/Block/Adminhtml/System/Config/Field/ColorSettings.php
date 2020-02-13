<?php

namespace TemplateMonster\ThemeOptions\Block\Adminhtml\System\Config\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class ColorSettings
 *
 * @package TemplateMonster\ThemeOptions\Block\Adminhtml\System\Config\Field
 */
class ColorSettings extends Field
{
    /**
     * @inheritdoc
     */
    protected function _getInheritCheckboxLabel(AbstractElement $element)
    {
        return __('Use Default Value');
    }
}