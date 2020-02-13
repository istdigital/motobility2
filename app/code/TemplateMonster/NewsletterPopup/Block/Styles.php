<?php

namespace TemplateMonster\NewsletterPopup\Block;

use Magento\Framework\View\Element\Template;
use TemplateMonster\NewsletterPopup\Helper\Data as NewsletterPopupHelper;

/**
 * NewsletterPopup styles block.
 *
 * @package TemplateMonster\NewsletterPopup\Block
 */
class Styles extends Template
{
    /**
     * @var NewsletterPopupHelper
     */
    protected $_helper;

    /**
     * @var string
     */
    protected $_template = 'styles.phtml';

    /**
     * Styles constructor.
     *
     * @param NewsletterPopupHelper $helper
     * @param Template\Context      $context
     * @param array                 $data
     */
    public function __construct(
        NewsletterPopupHelper $helper,
        Template\Context $context,
        array $data = []
    )
    {
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Get helper instance.
     *
     * @return NewsletterPopupHelper
     */
    public function getHelper()
    {
        return $this->_helper;
    }

    /**
     * Get button border color.
     *
     * @return null|string
     */
    public function getButtonBorderColor()
    {
        $color = $this->_helper->getButtonColor();

        return $color ? $this->_getRgba($color) : null;
    }

    /**
     * Convert color from hex to rgb format.
     *
     * @param string $color
     *
     * @return array
     */
    protected function _hexToRgb($color)
    {
        if ($color[0] == '#') {
            $color = substr($color, 1);
        }

        if (strlen($color) == 6) {
            list($red, $green, $blue) = [
                $color[0] . $color[1],
                $color[2] . $color[3],
                $color[4] . $color[5]
            ];
        } elseif (strlen($color) == 3) {
            list($red, $green, $blue) = [
                $color[0]. $color[0],
                $color[1]. $color[1],
                $color[2]. $color[2]
            ];
        } else {
            return false;
        }

        return [
            'red' => hexdec($red),
            'green' => hexdec($green),
            'blue' => hexdec($blue)
        ];
    }

    /**
     * Get RGBA.
     *
     * @param string $color
     *
     * @return string
     */
    protected function _getRgba($color)
    {
        $rgb = $this->_hexToRgb($color);

        return sprintf(
            'rgba(%s, %s, %s, 0.4);',
            $rgb['red'],
            $rgb['green'],
            $rgb['blue']
        );
    }
}