<?php

namespace TemplateMonster\Parallax\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 *
 * Data helper.
 *
 * @package TemplateMonster\Parallax\Helper
 */
class Data extends AbstractHelper
{
    /**
     * Enabled config option.
     */
    const XML_PATH_ENABLED = 'parallax/general/enabled';

    /**
     * Media image dir path.
     */
    const IMAGE_DIR  = 'parallax/images';

    /**
     * Media video dir path.
     */
    const VIDEO_DIR = 'parallax/videos';

    /**
     * Image max upload size in megabytes.
     */
    const IMAGE_MAX_SIZE = 2;

    /**
     * Video max upload size in megabytes.
     */
    const VIDEO_MAX_SIZE = 10;

    /**
     * Check is module enabled.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get list of available image formats.
     *
     * @return array
     */
    public function getAvailableImageFormats()
    {
        return ['jpg', 'jpeg', 'png', 'gif'];
    }

    /**
     * Get list of available video formats.
     *
     * @return array
     */
    public function getAvailableVideoFormats()
    {
        return ['mp4', 'webm'];
    }
}