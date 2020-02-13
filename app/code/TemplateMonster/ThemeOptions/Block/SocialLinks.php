<?php

namespace TemplateMonster\ThemeOptions\Block;

use TemplateMonster\ThemeOptions\Helper\Data as ThemeOptionsHelper;
use Magento\Framework\View\Element\Template;

/**
 * Social links block.
 *
 * @method string getPosition()
 *
 * @package TemplateMonster\ThemeOptions\Block
 */
class SocialLinks extends Template
{
    /**
     * @var string
     */
    protected $_template = 'social_links.phtml';

    /**
     * @var array
     */
    protected $_positions = ['header', 'footer'];

    /**
     * @var ThemeOptionsHelper
     */
    protected $_helper;

    /**
     * SocialLinks constructor.
     *
     * @param ThemeOptionsHelper $helper
     * @param Template\Context   $context
     * @param array              $data
     */
    public function __construct(
        ThemeOptionsHelper $helper,
        Template\Context $context,
        array $data
    )
    {
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->_helper->getShowSocialLinks($this->getData('position'));
    }

    /**
     * Get social icons align.
     *
     * @return string
     */
    public function getAlign()
    {
        return $this->_helper->getSocialPosition($this->getData('position'));
    }

    /**
     * Set block position.
     *
     * @param string $position
     * @return $this
     */
    public function setPosition($position)
    {
        if (!in_array($position, $this->_positions)) {
            throw new \InvalidArgumentException(sprintf('Invalid position "%s" provided.', $position));
        }
        $this->setData('position', $position);

        return $this;
    }

    /**
     * Check is image links.
     *
     * @return bool
     */
    public function isImageLinks()
    {
        return $this->_isType('image');
    }

    /**
     * Check is font links.
     *
     * @return bool
     */
    public function isFontLinks()
    {
        return $this->_isType('font');
    }

    /**
     * Get available social links.
     *
     * @return array
     */
    public function getAvailableSocialLinks()
    {
        if ($this->_isType('image')) {
            return $this->getAvailableImageLinks();
        }
        elseif ($this->_isType('font')) {
            return $this->getAvailableFontIcons();
        }

        return [];
    }

    /**
     * Get available social links.
     *
     * @return array
     */
    public function getAvailableImageLinks()
    {
        return $this->_helper->getImageIcon($this->getData('position'));
    }

    /**
     * Get available font icons.
     *
     * @return array
     */
    public function getAvailableFontIcons()
    {
        return $this->_helper->getFontIcon($this->getData('position'));
    }

    /**
     * Get links renderer.
     *
     * @return bool|\Magento\Framework\View\Element\AbstractBlock
     */
    public function getRenderer()
    {
        return $this->getChildBlock(sprintf('renderer.%s', $this->getType()));
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        if (!$this->hasData('position')) {
            throw new \RuntimeException('Social links position is not specified.');
        }

        if (!$this->_isShowSocialLinks() || !$this->_hasAvailableSocialLinks()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * @return bool
     */
    protected function _isShowSocialLinks()
    {
        return $this->_helper->getShowSocialLinks($this->getData('position'));
    }

    /**
     * Check if has available links.
     *
     * @return bool
     */
    protected function _hasAvailableSocialLinks()
    {
        return count($this->getAvailableSocialLinks()) > 0;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    protected function _isType($type)
    {
        return $this->getType() === $type;
    }
}