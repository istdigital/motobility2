<?php

namespace TemplateMonster\ThemeOptions\Plugin\LayoutSwitcher\Helper;

use TemplateMonster\ThemeOptions\Helper\Data as ThemeOptionsHelper;
use TemplateMonster\ThemeOptions\Helper\ColorScheme as ColorSchemeHelper;
use TemplateMonster\LayoutSwitcher\Helper\Data as LayoutSwitcherHelper;

/**
 * Class DataPlugin.
 *
 * @package TemplateMonster\ThemeOptions\Plugin\ThemeOptions\Helper
 */
class DataPlugin
{
    /**
     * @var ThemeOptionsHelper
     */
    protected $_themeOptionsHelper;

    /**
     * @var ColorSchemeHelper
     */
    protected $_colorSchemeHelper;

    /**
     * DataPlugin constructor.
     *
     * @param ThemeOptionsHelper $themeOptionsHelper
     * @param ColorSchemeHelper  $colorSchemeHelper
     */
    public function __construct(
        ThemeOptionsHelper $themeOptionsHelper,
        ColorSchemeHelper $colorSchemeHelper
    )
    {
        $this->_themeOptionsHelper = $themeOptionsHelper;
        $this->_colorSchemeHelper = $colorSchemeHelper;
    }

    /**
     * After get color schemes method plugin.
     *
     * @param LayoutSwitcherHelper $subject
     * @param array|null           $result
     *
     * @return mixed
     */
    public function afterGetColorSchemes(LayoutSwitcherHelper $subject, $result)
    {
        return $result ?: $this->_colorSchemeHelper->getColorSchemes();
    }

    /**
     * Around get default color scheme method plugin.
     *
     * @param LayoutSwitcherHelper $subject
     * @param \Closure             $proceed
     * @param null                 $theme
     *
     * @return mixed
     */
    public function aroundGetDefaultColorScheme(LayoutSwitcherHelper $subject, \Closure $proceed, $theme = null)
    {
        return $this->_themeOptionsHelper->getColorScheme($theme) ?: $proceed();
//        return $result ?: $this->_themeOptionsHelper->getColorScheme($theme);
    }
}