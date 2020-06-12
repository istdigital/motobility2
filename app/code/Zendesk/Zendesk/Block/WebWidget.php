<?php

namespace Zendesk\Zendesk\Block;

use Magento\Framework\View\Element\Template;

class WebWidget extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Zendesk\Zendesk\Helper\Config
     */
    protected $configHelper;
    /**
     * @var \Zendesk\Zendesk\Helper\WebWidget
     */
    protected $webWidgetHelper;

    /**
     * WebWidget constructor.
     * @param Template\Context $context
     * @param \Zendesk\Zendesk\Helper\Config $configHelper
     * @param \Zendesk\Zendesk\Helper\WebWidget $webWidgetHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        // end parent parameters
        \Zendesk\Zendesk\Helper\Config $configHelper,
        \Zendesk\Zendesk\Helper\WebWidget $webWidgetHelper,
        // end custom parameters
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configHelper = $configHelper;
        $this->webWidgetHelper = $webWidgetHelper;
    }

    /**
     * Get web widget snippet HTML, if enabled
     *
     * @return string
     */
    public function getWebWidgetSnippetHtml()
    {
        if (!$this->configHelper->getWebWidgetEnabled()) {
            return '';
        }

        return $this->webWidgetHelper->getWebWidgetSnippet();
    }
}
