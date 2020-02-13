<?php

namespace TemplateMonster\Parallax\Block\Adminhtml\Form\Element;

use TemplateMonster\Parallax\Helper\Data as ParallaxHelper;
use Magento\Framework\Data\Form\Element\Image as CoreImage;

/**
 * Image form type.
 *
 * @package TemplateMonster\Parallax\Block\Adminhtml\Form\Element
 */
class Image extends CoreImage
{
    /**
     * @inheritdoc
     */
    protected function _getUrl()
    {
        return sprintf('%s/%s', ParallaxHelper::IMAGE_DIR, parent::_getUrl());
    }
}