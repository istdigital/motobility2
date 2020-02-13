<?php

namespace TemplateMonster\ThemeOptions\Block\Widget\Grid\Column\Renderer;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\DataObject;

/**
 * Social icons image renderer.
 *
 * @method string getInputName()
 * @method string getInputId()
 * @method string getColumnName()
 * @method array  getColumn()
 *
 * @package TemplateMonster\ThemeOptions\Block\Widget\Grid\Column\Renderer
 */
class Image extends Template
{
    /**
     * @var string
     */
    protected $_template = 'renderer/image.phtml';

    /**
     * Get social icons base url
     *
     * @return string
     */
    public function getIconsBaseUrl()
    {
        $mediaUrl = $this->_urlBuilder->getBaseUrl([
            '_type' => UrlInterface::URL_TYPE_MEDIA
        ]);

        return $mediaUrl . 'theme_options/social_icons/';
    }
}