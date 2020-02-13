<?php

namespace TemplateMonster\SocialSharing\Block\AddThis;

use TemplateMonster\SocialSharing\Helper\Data as SocialSharingHelper;
use Magento\Framework\View\Element\Template;

/**
 * AddThis Icons Block.
 */
class Icons extends Template
{
    /**
     * @var SocialSharingHelper
     */
    protected $_helper;

    /**
     * Icons constructor.
     *
     * @param SocialSharingHelper $helper
     * @param Template\Context    $context
     * @param array               $data
     */
    public function __construct(
        SocialSharingHelper $helper,
        Template\Context $context,
        array $data = []
    ) {
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Get custom button image.
     *
     * @return string
     */
    public function getCustomButton()
    {
        return $this->_helper->getCustomButton();
    }

    /**
     * Get custom buttons code.
     *
     * @return string
     */
    public function getCustomCode()
    {
        return $this->_helper->getCustomCode();
    }

    /**
     * Get custom metadata attributes.
     *
     * @return string
     */
    public function getCustomMetadata()
    {
        $attributes = '';
        foreach ($this->_helper->getCustomMetadata() as $name => $value) {
            $attributes .= sprintf(' addthis:%s="%s"', $name, $value);
        }

        return $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        return sprintf('styles/%s.phtml', $this->_helper->getStyle());
    }
}
