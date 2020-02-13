<?php

namespace TemplateMonster\NewsletterPopup\Block\Widget;

use Magento\Newsletter\Block\Subscribe as BaseSubscribe;
use Magento\Widget\Block\BlockInterface;

/**
 * Newsletter pop-up subscribe widget.
 *
 * @package TemplateMonster\NewsletterPopup\Block\Widget
 */
class Subscribe extends BaseSubscribe implements BlockInterface
{
    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
       return $this->getData('title');
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
       return $this->getData('description');
    }

    /**
     * Get unique id.
     *
     * @return string
     */
    public function getUniqueId()
    {
        return uniqid('newsletter-popup-subscribe');
    }
}