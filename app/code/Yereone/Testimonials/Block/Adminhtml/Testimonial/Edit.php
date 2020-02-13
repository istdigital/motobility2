<?php

namespace Yereone\Testimonials\Block\Adminhtml\Testimonial;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Block constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Yereone_Testimonials';
        $this->_controller = 'adminhtml_testimonial';
        parent::_construct();

        $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                    ],
                ]
            ],
            -100
        );
    }
}
