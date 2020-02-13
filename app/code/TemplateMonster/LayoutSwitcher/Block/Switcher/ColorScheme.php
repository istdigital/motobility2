<?php

namespace TemplateMonster\LayoutSwitcher\Block\Switcher;

use TemplateMonster\LayoutSwitcher\Block\AbstractBlock;

/**
 * Color schemes block.
 *
 * @package TemplateMonster\LayoutSwitcher\Block\Switcher
 */
class ColorScheme extends AbstractBlock
{
    /**
     * @var string
     */
    protected $_template = 'switcher/color_scheme.phtml';

    /**
     * Get color schemes.
     *
     * @return array
     */
    public function getColorSchemes()
    {
        return $this->_helper->getColorSchemes();
    }

    /**
     * Check if has color schemes.
     *
     * @return bool
     */
    public function hasColorSchemes()
    {
        return count($this->getColorSchemes()) > 0;
    }

    /**
     * Check if current color scheme.
     *
     * @param $colorScheme
     *
     * @return bool
     */
    public function isCurrentColorScheme($theme, $colorScheme)
    {
        return $colorScheme === $this->_helper->getCurrentColorScheme($theme);
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        if (!$this->hasColorSchemes()) {
            return '';
        }

        return parent::_toHtml();
    }
}