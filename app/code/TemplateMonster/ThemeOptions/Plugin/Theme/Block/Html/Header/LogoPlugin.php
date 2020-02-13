<?php

namespace TemplateMonster\ThemeOptions\Plugin\Theme\Block\Html\Header;

use \Magento\Theme\Block\Html\Header\Logo;
use \TemplateMonster\ThemeOptions\Helper\Data;

/**
 * Config edit plugin.
 *
 * @package TemplateMonster\ThemeOptions\Plugin\Theme\Block\Html\Header
 */
class LogoPlugin
{
    /**
     * Config sections.
     *
     * @var helper
     */
    protected $_helper;

    /**
     * @param \TemplateMonster\ThemeOptions\Helper\Data $helper
     *
     */
    public function __construct(
        Data $helper
    ) {
        $this->_helper = $helper;
    }

    /**
     * Get logo image URL
     *
     * @return string
     */
    public function aroundGetLogoSrc(Logo $subject, callable $proceed)
    {
        $logo = $this->_helper->getLogoUrl();
        return $logo ? $logo : $proceed();
    }

    /**
     * Get logo image width
     *
     * @return string
     */
    public function aroundGetLogoWidth(Logo $subject, callable $proceed)
    {
        $logoWidth = $this->_helper->getLogoWidth();
        return $logoWidth ? $logoWidth : $proceed();
    }

    /**
     * Get logo image height
     *
     * @return string
     */
    public function aroundGetLogoHeight(Logo $subject, callable $proceed)
    {
        $logoHeight = $this->_helper->getLogoHeight();
        return $logoHeight ? $logoHeight : $proceed();
    }

    /**
     * Get logo alt
     *
     * @return string
     */
    public function aroundGetLogoAlt(Logo $subject, callable $proceed)
    {
        $logoAlt = $this->_helper->getLogoAlt();
        return $logoAlt ? $logoAlt : $proceed();
    }

}