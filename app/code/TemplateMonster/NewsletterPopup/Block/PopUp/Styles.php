<?php

namespace TemplateMonster\NewsletterPopup\Block\PopUp;

use TemplateMonster\NewsletterPopup\Helper\Data as PopupHelper;
use Magento\Framework\View\Element\Template;

/**
 * Pop-Up styles block.
 *
 * @package TemplateMonster\NewsletterPopup\Block\PopUp
 */
class Styles extends Template
{
    /**
     * @var PopupHelper
     */
    protected $_popupHelper;

    /**
     * @var string
     */
    protected $_template = 'popup/styles.phtml';

    /**
     * Styles constructor.
     *
     * @param PopupHelper      $popupHelper
     * @param Template\Context $context
     * @param array            $data
     */
    public function __construct(
        PopupHelper $popupHelper,
        Template\Context $context,
        array $data
    )
    {
        $this->_popupHelper = $popupHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get popup width.
     *
     * @return int
     */
    public function getPopupWidth()
    {
        return $this->_popupHelper->getPopupWidth();
    }

    /**
     * Get button color.
     *
     * @return string
     */
    public function getButtonColor()
    {
        return $this->_popupHelper->getButtonColor();
    }

    /**
     * Get button hover color.
     *
     * @return string
     */
    public function getButtonHoverColor()
    {
        return $this->_popupHelper->getButtonHoverColor();
    }

    /**
     * Get button border color.
     *
     * @return string
     */
    public function getButtonBorderColor()
    {
        $color = $this->_popupHelper->getButtonColor();
        $html = null;
        if ($color) {
            $html = 'rgba(' . $this->hexToRgb($color)['red'] . ', ' . $this->hexToRgb($color)['green'] . ', ' . $this->hexToRgb($color)['blue'] . ', 0.4);';
        }
        return $html;
    }

    public function getAvailableIcons()
    {
        return $this->_popupHelper->getAvailableIcons();
    }

    /**
     * Get icon background color in hex format.
     *
     * @param string $type
     *
     * @return string
     */
    public function getIconBackground($type)
    {
        return $this->_popupHelper->getIconBackground($type);
    }

    /**
     * Get icon color in hex format.
     *
     * @param string $type
     *
     * @return string
     */
    public function getIconColor($type)
    {
        return $this->_popupHelper->getIconColor($type);
    }

    /**
     * Get icon background hover color in hex format.
     *
     * @param string $type
     *
     * @return string
     */
    public function getIconBackgroundHover($type)
    {
        return $this->_popupHelper->getIconBackgroundHover($type);
    }

    /**
     * Get icon hover color in hex format.
     *
     * @param string $type
     *
     * @return string
     */
    public function getIconHoverColor($type)
    {
        return $this->_popupHelper->getIconHoverColor($type);
    }

    /**
     * Convert color from hex to rgb format.
     *
     * @param string $color
     *
     * @return array
     */
    protected function hexToRgb($color)
    {
        if ($color[0] == '#') {
            $color = substr($color, 1);
        }

        if (strlen($color) == 6) {
            list($red, $green, $blue) = array(
                $color[0] . $color[1],
                $color[2] . $color[3],
                $color[4] . $color[5]
            );
        } elseif (strlen($color) == 3) {
            list($red, $green, $blue) = array(
                $color[0]. $color[0],
                $color[1]. $color[1],
                $color[2]. $color[2]
            );
        } else {
            return false;
        }

        $red = hexdec($red);
        $green = hexdec($green);
        $blue = hexdec($blue);

        return array(
            'red' => $red,
            'green' => $green,
            'blue' => $blue
        );
    }
}