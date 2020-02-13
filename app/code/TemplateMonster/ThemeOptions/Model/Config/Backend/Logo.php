<?php

namespace TemplateMonster\ThemeOptions\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\Image as BaseImage;

/**
 * Logo backend model.
 *
 * @package TemplateMonster\ThemeOptions\Model\Config\Backend
 */
class Logo extends BaseImage
{
    protected function _getAllowedExtensions()
    {
        return ['jpg', 'jpeg', 'gif', 'png', 'svg'];
    }
}