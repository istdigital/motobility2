<?php

namespace TemplateMonster\ThemeOptions\Plugin\Block\Switcher;

use TemplateMonster\LayoutSwitcher\Block\Switcher\ColorScheme;
use TemplateMonster\ThemeOptions\Helper\ColorScheme as ColorSchemeHelper;

/**
 * ColorScheme block plugin.
 *
 * @package TemplateMonster\ThemeOptions\Model
 */
class ColorSchemePlugin
{
    /**
     * @var ColorSchemeHelper
     */
    protected $_colorSchemeHelper;

    /**
     * ColorSchemePlugin constructor.
     *
     * @param ColorSchemeHelper $colorSchemeHelper
     */
    public function __construct(ColorSchemeHelper $colorSchemeHelper)
    {
        $this->_colorSchemeHelper = $colorSchemeHelper;
    }

    /**
     * Get color schemes method plugin.
     *
     * @param ColorScheme $subject
     * @param array $result
     *
     * @return array
     */
    public function afterGetColorSchemes(ColorScheme $subject, $result)
    {
        if (count($result) === 0) {
            return $this->_colorSchemeHelper->getStoreColorSchemes();
        }

        return $result;
    }
}