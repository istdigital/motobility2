<?php

namespace Zendesk\Zendesk\Block\Adminhtml\System\Config;

class WebWidgetCustomize extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Zendesk\Zendesk\Helper\Config
     */
    protected $configHelper;

    /**
     * WebWidgetCustomize constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Zendesk\Zendesk\Helper\Config $configHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        // end parent required parameters
        \Zendesk\Zendesk\Helper\Config $configHelper,
        // end custom parameters
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configHelper = $configHelper;
    }

    /**
     * Set template
     *
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setTemplate('Zendesk_Zendesk::system/config/web-widget-customize-link.phtml');
        return $this;
    }

    /**
     * Unset irrelevant element data
     *
     * {@inheritdoc}
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element = clone $element;
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get dynamic web widget customization URL
     *
     * @return string
     */
    public function getWebWidgetCustomizationUrl()
    {
        $domain = $this->configHelper->getDomain();

        if (empty($domain)) {
            return ''; // Cannot compute customization URL without domain
        }

        return str_replace(
            \Zendesk\Zendesk\Helper\Config::DOMAIN_PLACEHOLDER,
            $domain,
            $this->configHelper->getWebWidgetCustomizeUrlPattern()
        );
    }

    /**
     * Get element output
     *
     * {@inheritdoc}
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }
}
