<?php

namespace TemplateMonster\SocialLogin\Block\Adminhtml\System\Config\Field;

use TemplateMonster\SocialLogin\Helper\Data as SocialLoginHelper;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * RedirectUri frontend model.
 */
class RedirectUri extends Field
{
    /**
     * @var SocialLoginHelper
     */
    protected $_helper;

    /**
     * Social login provider.
     *
     * @var string
     */
    protected $_provider;

    /**
     * RedirectUri constructor.
     *
     * @param SocialLoginHelper $helper
     * @param Context           $context
     * @param string            $provider
     * @param array             $data
     */
    public function __construct(
        SocialLoginHelper $helper,
        Context $context,
        $provider,
        array $data = []
    )
    {
        $this->_helper = $helper;
        $this->_provider = $provider;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $element->setValue(
            $this->_helper->getRedirectUri($this->_provider)
        );

        return parent::_getElementHtml($element);
    }
}
