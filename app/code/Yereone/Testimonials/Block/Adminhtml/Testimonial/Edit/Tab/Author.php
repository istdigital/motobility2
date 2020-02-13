<?php

namespace Yereone\Testimonials\Block\Adminhtml\Testimonial\Edit\Tab;

use Yereone\Testimonials\Helper\Data;

class Author extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var string
     */
    protected $author = '';

    /**
     * @var string
     */
    protected $email = '';

    /**
     * @var string
     */
    protected $job = '';

    /**
     * @var string
     */
    protected $city = '';

    /**
     * @var string
     */
    protected $company = '';

    
    protected $telephone = '';

    /**
     * @var string
     */
    protected $company_url = '';

    /**
     * @var string
     */
    protected $image = '';

    /**
     * @var string
     */
    protected $facebook_url = '';

    /**
     * @var string
     */
    protected $twitter_url = '';

    /**
     * @var string
     */
    protected $linkedin_url = '';

    /**
     * @var string
     */
    protected $googleplus_url = '';

    /**
     * @var string
     */
    protected $youtube_url = '';

    /**
     * Create a form element with necessary controls
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('testimonial_model');
        $form = $this->_formFactory->create();
        if ($this->getRequest()->getParam('id')) {
            $this->author = $model->getAuthor();
            $this->email = $model->getEmail();
            $this->telephone = $model->getTelephone();
            $this->job = $model->getJob();
            $this->city = $model->getCity();
            $this->company = $model->getCompanyName();
            $this->company_url = $model->getCompanyUrl();
            if ($model->getImage()) {
                $this->image = Data::MEDIA_PATH . $model->getImage();
            }
            $this->facebook_url = $model->getFacebookUrl();
            $this->twitter_url = $model->getTwitterUrl();
            $this->linkedin_url = $model->getLinkedinUrl();
            $this->googleplus_url = $model->getGoogleplusUrl();
            $this->youtube_url = $model->getYoutubeUrl();
        }
        $authorFieldset = $form->addFieldset(
            'author_fieldset',
            ['legend' => __('Author Information'), 'class' => 'fieldset-wide']
        );

        $authorFieldset->addField(
            'author',
            'text',
            [
                'label'   => __('Author Name'),
                'title'   => __('Author Name'),
                'class'   => 'required-entry',
                'required'=> true,
                'name'    => 'author',
                'value'   => $this->author,
            ]
        );

        $authorFieldset->addField(
            'email',
            'text',
            [
                'label'  => __('Email'),
                'title'  => __('Email'),
                'name'   => 'email',
                'class'  => 'validate-email',
                'value'  => $this->email,
            ]
        );

        $authorFieldset->addField(
            'telephone',
            'text',
            [
                'label'  => __('Telephone'),
                'title'  => __('Telephone'),
                'name'   => 'telephone',
                'value'  => $this->telephone,
            ]
        );

        $authorFieldset->addField(
            'job',
            'text',
            [
                'label'  => __('Job'),
                'title'  => __('Job'),
                'name'   => 'job',
                'value'  => $this->job,
            ]
        );

        $authorFieldset->addField(
            'city',
            'text',
            [
                'label'  => __('City'),
                'title'  => __('City'),
                'name'   => 'city',
                'value'  => $this->city,
            ]
        );

        $authorFieldset->addField(
            'company_name',
            'text',
            [
                'label'  => __('Company'),
                'title'  => __('Company'),
                'name'   => 'company_name',
                'value'  => $this->company,
            ]
        );

        $authorFieldset->addField(
            'company_url',
            'text',
            [
                'label'  => __('Company Website URL'),
                'title'  => __('Company Website URL'),
                'name'   => 'company_url',
                'value'  => $this->company_url,
            ]
        );

        $authorFieldset->addField(
            'image',
            'image',
            [
                'label'  => __('Author Image'),
                'title'  => __('Author Image'),
                'name'   => 'image',
                'value'  => $this->image,
                'note' => 'Allow image type: jpg, jpeg, gif, png',
            ]
        );

        $authorFieldset->addField(
            'facebook_url',
            'text',
            [
                'label'  => __('Facebook URL'),
                'title'  => __('Facebook URL'),
                'name'   => 'facebook_url',
                'value'  => $this->facebook_url,
            ]
        );

        $authorFieldset->addField(
            'twitter_url',
            'text',
            [
                'label'  => __('Twitter URL'),
                'title'  => __('Twitter URL'),
                'name'   => 'twitter_url',
                'value'  => $this->twitter_url,
            ]
        );

        $authorFieldset->addField(
            'linkedin_url',
            'text',
            [
                'label'  => __('Linkedin URL'),
                'title'  => __('Linkedin URL'),
                'name'   => 'linkedin_url',
                'value'  => $this->linkedin_url,
            ]
        );

        $authorFieldset->addField(
            'googleplus_url',
            'text',
            [
                'label'  => __('Google Plus URL'),
                'title'  => __('Google Plus URL'),
                'name'   => 'googleplus_url',
                'value'  => $this->googleplus_url,
            ]
        );

        $authorFieldset->addField(
            'youtube_url',
            'text',
            [
                'label'  => __('Youtube URL'),
                'title'  => __('Youtube URL'),
                'name'   => 'youtube_url',
                'value'  => $this->youtube_url,
            ]
        );

        $this->setForm($form);
        parent::_prepareForm();
        return $this;
    }
}
