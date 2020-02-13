<?php

namespace Yereone\Testimonials\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Yereone\Testimonials\Helper\Data;

class TestimonialForm extends Template implements BlockInterface
{
    /**
     * @var \Yereone\Testimonials\Helper\Data
     */
    protected $helper;

    /**
     * Path to template
     *
     * @var string
     */
    protected $_template = "widget/testimonial_form.phtml";

    /**
     * @param Template\Context $context
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Yereone\Testimonials\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * Returns action url for testimonial form
     *
     * @return string
     */
    public function getFormAction()
    {
        return $this->getUrl('testimonial/index/save', ['_secure' => true]);
    }

    /**
     * Get widget title
     *
     * @return string
     */
    public function getWidgetTitle()
    {
        return $this->getData('widget_title');
    }

    /**
     * Show testimonial rating
     *
     * @return bool
     */
    public function showRating()
    {
        return $this->getRating();
    }

    /**
     * Show testimonial author
     *
     * @return bool
     */
    public function showAuthor()
    {
        return $this->getAuthor();
    }

    /**
     * Show testimonial author job
     *
     * @return bool
     */
    public function showJob()
    {
        return $this->getJob();
    }

    /**
     * Show testimonial author city
     *
     * @return bool
     */
    public function showCity()
    {
        return $this->getCity();
    }

    /**
     * Show testimonial author company
     *
     * @return bool
     */
    public function showCompany()
    {
        return $this->getCompany();
    }

    /**
     * Show testimonial author image
     *
     * @return bool
     */
    public function showImage()
    {
        return $this->getImage();
    }

    /**
     * Show socials
     *
     * @return bool
     */
    public function showSocial()
    {
        return $this->getSocial();
    }

    /**
     * Show Recaptcha
     *
     * @return bool
     */
    public function showRecaptcha()
    {
        if ($this->getRecaptcha() && $this->helper->isRecaptchaEnabled()) {
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getRecaptchaSiteKey()
    {
        return $this->helper->getRecaptchaSiteKey();
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->helper->isModuleEnabled()) {
            return '';
        }
        return parent::_toHtml();
    }
}
