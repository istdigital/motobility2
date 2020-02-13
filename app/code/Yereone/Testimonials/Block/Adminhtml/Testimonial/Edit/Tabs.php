<?php

namespace Yereone\Testimonials\Block\Adminhtml\Testimonial\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Initialize integration edit page tabs
     *
     * @return void
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('testimonial_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('General'));
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeToHtml()
    {
        $this->addTab('general', [
            'label' => __('General'),
            'title' => __('General'),
            'content' => $this->getLayout()->createBlock('Yereone\Testimonials\Block\Adminhtml\Testimonial\Edit\Tab\General')->toHtml(),
            'active' => true
        ]);

        $this->addTab('author', [
            'label' => __('Author Information'),
            'title' => __('Author Information'),
            'content' => $this->getLayout()->createBlock('Yereone\Testimonials\Block\Adminhtml\Testimonial\Edit\Tab\Author')->toHtml(),
            'active' => false
        ]);

        return parent::_beforeToHtml();
    }
}
