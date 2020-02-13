<?php

namespace TemplateMonster\ThemeOptions\Block\SocialLinks\Renderer;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\View\Element\Template;

/**
 * Social links image renderer.
 *
 * @package TemplateMonster\ThemeOptions\Block\SocialLinks\Renderer
 */
class Image extends Template
{
    /**
     * @var string
     */
    protected $_template = 'renderer/image.phtml';

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
     * Get width.
     *
     * @return int
     */
    public function getWidth()
    {
        return (int) $this->getData('width');
    }

    /**
     * Get alt text.
     *
     * @return string
     */
    public function getAltText()
    {
        return $this->getData('alt_text');
    }

    /**
     * Get image url
     *
     * @return string
     */
    public function getImageUrl()
    {
        $media = $this->_urlBuilder->getBaseUrl([
            '_type' => UrlInterface::URL_TYPE_MEDIA
        ]);

        return sprintf('%s%s%s', $media, 'theme_options/social_icons/', $this->getData('image'));
    }
}