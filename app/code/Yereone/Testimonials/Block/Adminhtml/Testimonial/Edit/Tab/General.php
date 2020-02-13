<?php

namespace Yereone\Testimonials\Block\Adminhtml\Testimonial\Edit\Tab;

class General extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $testimonial = '';

    /**
     * @var string
     */
    protected $status_id = '2';

    /**
     * @var string
     */
    protected $rating = '';

    /**
     * @var \Yereone\Testimonials\Model\Config\Source\Rating
     */
    protected $ratingStars;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Yereone\Testimonials\Model\Config\Source\Rating $ratingStars
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Yereone\Testimonials\Model\Config\Source\Rating $ratingStars,
        array $data = []
    ) {
        $this->ratingStars = $ratingStars;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Create a form element with necessary controls
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('testimonial_model');
        $form = $this->_formFactory->create();
        $generalFieldset = $form->addFieldset(
            'general',
            ['legend' => __('General'), 'class' => 'fieldset-wide']
        );

        if ($this->getRequest()->getParam('id')) {
            $this->title = $model->getTitle();
            $this->testimonial = $model->getTestimonialContent();
            $this->status_id = $model->getStatusId();
            $this->rating= $model->getRating();
            
            $generalFieldset->addField(
                'id',
                'hidden',
                [
                    'name'   => 'id',
                    'values' => ['0' => __('Disabled'), '1' =>__('Enabled')],
                    'value'  => $model->getId()
                ]
            );
        }

        $generalFieldset->addField(
            'status_id',
            'select',
            [
                'label'  => __('Status'),
                'title'  => __('Status'),
                'name'   => 'status_id',
                'values' => ['1' => __('Approved'), '2' =>__('Pending'), '3' =>__('Not Approved')],
                'value'  => $this->status_id
            ]
        );

        $generalFieldset->addField(
            'title',
            'text',
            [
                'label'    => __('Title'),
                'title'    => __('Title'),
                'class'    => 'required-entry',
                'required' => true,
                'name'     => 'title',
                'value'    => $this->title,
            ]
        );

        $generalFieldset->addField(
            'testimonial_content',
            'textarea',
            [
                'label'    => __('Testimonial Content'),
                'title'    => __('Testimonial Content'),
                'class'    => 'required-entry',
                'required' => true,
                'name'     => 'testimonial_content',
                'value'    => $this->testimonial,
            ]
        );

        $generalFieldset->addField(
            'rating',
            'select',
            [
                'label'  => __('Rating'),
                'title'  => __('Rating'),
                'name'   => 'rating',
                'values' => $this->ratingStars->toOptionArray(),
                'value'  => $this->rating,
            ]
        );

        $this->setForm($form);
        parent::_prepareForm();
        return $this;
    }
}
