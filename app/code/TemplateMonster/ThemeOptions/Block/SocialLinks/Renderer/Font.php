<?php

namespace TemplateMonster\ThemeOptions\Block\SocialLinks\Renderer;

use Magento\Framework\View\Element\Template;

/**
 * Social links font renderer.
 *
 * @package TemplateMonster\ThemeOptions\Block\SocialLinks\Renderer
 */
class Font extends Template
{
    protected $_template = 'renderer/font.phtml';

    /**
     * Get CSS-class.
     *
     * @return string
     */
    public function getCssClass()
    {
        return $this->getData('css_class');
    }

    /**
     * Get social url.
     *
     * @return string
     */
    public function getSocialUrl()
    {
        return $this->getData('social_url');
    }

    /**
     * Get font size
     *
     * @return int
     */
    public function getFontSize()
    {
        return (int) $this->getData('font_size');
    }

    /**
     * Get line height.
     *
     * @return int
     */
    public function getLineHeight()
    {
        return (int) $this->getData('line_height');
    }
}