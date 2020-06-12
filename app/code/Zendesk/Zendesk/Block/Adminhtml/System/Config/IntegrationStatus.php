<?php

namespace Zendesk\Zendesk\Block\Adminhtml\System\Config;

use Magento\Framework\Exception\NoSuchEntityException;

class IntegrationStatus extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Zendesk\Zendesk\Helper\Integration
     */
    protected $integrationHelper;

    /**
     * IntegrationStatus constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Zendesk\Zendesk\Helper\Integration $integrationHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        // end parent parameters
        \Zendesk\Zendesk\Helper\Integration $integrationHelper,
        // end custom parameters
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->integrationHelper = $integrationHelper;
    }

    /**
     * Set template
     *
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setTemplate('Zendesk_Zendesk::system/config/integration-status.phtml');
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
     * Get element output
     *
     * {@inheritdoc}
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $integrationMessage = __('Integration not configured.');
        $integrationActionUrl = $this->getUrl('zendesk/system_config/fixIntegration');

        try {
            $integration = $this->integrationHelper->getIntegration();

            $integrationMessage = __('Integration successfully configured.');
            $integrationActionUrl = null;
        } catch (NoSuchEntityException $e) {
        }

        $this->addData(
            [
                'action_url' => $integrationActionUrl,
                'button_label' => $integrationMessage,
                'html_id' => $element->getHtmlId()
            ]
        );

        return $this->_toHtml();
    }
}
