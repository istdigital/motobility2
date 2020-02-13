<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
namespace Manadev\Core\Blocks\Adminhtml\ExtensionControl\Feature;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\Select;
use Magento\Framework\DataObject;

class IsEnabledColumn extends Select
{
    public function render(DataObject $row) {
        if ($row->getData('disabled_by_dependency')) {
            return __('Disabled by dependency');
        }
        if ($this->getStore()) {
            $options = [
                'use_default' => $row->getData('globally_manually_disabled')
                        ? __('Disabled (default)')
                        : __('Enabled (default)'),
                'enabled' => __('Enabled in this store'),
                'disabled' => __('Disabled in this store'),
            ];

            $value = $row->hasData('locally_disabled')
                ? ($row->getData('locally_disabled') ? 'disabled' : 'enabled')
                : 'use_default';
        }
        else {
            $options = [
                'enabled' => __('Enabled'),
                'disabled' => __('Disabled'),
            ];
            $value = $row->getData('disabled') ? 'disabled' : 'enabled';
        }

        $name = $this->getColumn()->getName() ? $this->getColumn()->getName() : $this->getColumn()->getId();
        $html = '<select name="' . $this->escapeHtml($name) . '" ' . $this->getColumn()->getValidateClass() .
            'data-feature="' . $row->getData('name') . '">';

        foreach ($options as $val => $label) {
            $selected = $val == $value && $value !== null ? ' selected="selected"' : '';
            $html .= '<option value="' . $this->escapeHtml($val) . '"' . $selected . '>';
            $html .= $this->escapeHtml($label) . '</option>';
        }
        $html .= '</select>';
        return $html;
    }

    protected function getStore() {
        return $this->getRequest()->getParam('store', 0);
    }
}