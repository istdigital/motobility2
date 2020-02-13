<?php

namespace Yereone\Testimonials\Block\Adminhtml\Testimonial\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Initialize testimonial form
     *
     * @return \Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Form|\Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl('testimonial/testimonial/save'),
                    'enctype' => 'multipart/form-data',
                    'method' => 'post',
                ],
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
