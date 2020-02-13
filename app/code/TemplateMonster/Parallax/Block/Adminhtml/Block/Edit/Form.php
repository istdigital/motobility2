<?php

namespace TemplateMonster\Parallax\Block\Adminhtml\Block\Edit;

use Magento\Backend\Block\Widget\Form\Generic;

/**
 * Block edit form.
 *
 * @package TemplateMonster\Parallax\Block\Adminhtml\Block\Edit
 */
class Form extends Generic
{
    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create([
            'data' => [
                'id' => 'edit_form',
                'method' => 'post',
                'action' => $this->getData('action'),
            ],
        ]);
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
