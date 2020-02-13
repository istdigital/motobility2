<?php

namespace TemplateMonster\ThemeOptions\Plugin\Theme\Model\Favicon;

use \Magento\Theme\Model\Favicon\Favicon;
use \TemplateMonster\ThemeOptions\Helper\Data;

/**
 * Config edit plugin.
 *
 * @package TemplateMonster\ThemeOptions\Plugin\Theme\Model\Favicon
 */
class FaviconPlugin
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
     * Get favicon
     *
     * @return string
     */
    public function aroundGetFaviconFile(Favicon $subject, callable $proceed)
    {
        $favicon = $this->_helper->getFaviconUrl();
        return $favicon ? $favicon : $proceed();
    }

}