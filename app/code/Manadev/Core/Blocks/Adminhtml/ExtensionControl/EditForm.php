<?php
/** 
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core\Blocks\Adminhtml\ExtensionControl;

class EditForm extends \Magento\Backend\Block\Widget\Form\Generic
{
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $form->setHtmlIdPrefix('block_');
        $form->setUseContainer(true);
        $form->addField('feature', 'hidden', ['name' => 'feature']);
        $form->addField('is_enabled', 'hidden', ['name' => 'is_enabled']);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId) {
        return $this->_authorization->isAllowed($resourceId);
    }

    protected function _prepareLayout() {
        $result = parent::_prepareLayout();
        $this->addChild('extensionGrid', 'Manadev\Core\Blocks\Adminhtml\ExtensionControl\Grid');
        return $result;
    }

    /**
     * Processing block html after rendering
     *
     * @param   string $html
     * @return  string
     */
    protected function _afterToHtml($html) {
        $html .= $this->getChildHtml('extensionGrid');
        return parent::_afterToHtml($html);
    }


}