<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core\Blocks\Adminhtml;

use Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element;

class FieldRenderer extends Element
{
    protected $_template = 'Manadev_Core::field2.phtml';

    public function getUseDefaultLabel() {
        $setting = $this->_request->getParam('store') ? 'store_level_use_default_label': 'global_use_default_label';

        return $this->getElement()->getData($setting);
    }

    public function getUseDefaultValue() {
        $edit = $this->getElement()->getForm()->getData('edit');

        return !isset($edit[$this->getElement()->getData('name')]) || $edit[$this->getElement()->getData('name')] === null;
    }
}