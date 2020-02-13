<?php

namespace TemplateMonster\ThemeOptions\Block\Adminhtml\System\Config\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Backend system config array field renderer
 */
class FontSocialIcons extends AbstractFieldArray
{
    /**
     * @inheritdoc
     */
    protected function _prepareToRender()
    {
        $this->addColumn('css_class',   ['label' => __('CSS Class')]);
        $this->addColumn('social_url',  ['label' => __('Social URL')]);
        $this->addColumn('font_size',   ['label' => __('Font Size')]);
        $this->addColumn('line_height', ['label' => __('Line Height')]);
        $this->_addButtonLabel = __('Add Icon');
        $this->_addAfter = false;
    }
}
