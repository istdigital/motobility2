<?php

namespace Zendesk\Zendesk\Block\Adminhtml\System\Config;

class TestConnection extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Zendesk\Zendesk\Helper\Api
     */
    protected $apiHelper;
    /**
     * @var \Zendesk\Zendesk\Helper\Data
     */
    protected $helper;

    /**
     * TestConnection constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Zendesk\Zendesk\Helper\Api $apiHelper
     * @param \Zendesk\Zendesk\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        // end parent parameters
        \Zendesk\Zendesk\Helper\Api $apiHelper,
        \Zendesk\Zendesk\Helper\Data $helper,
        // end custom parameters
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->apiHelper = $apiHelper;
        $this->helper = $helper;
    }

    /**
     * Set template
     *
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setTemplate('Zendesk_Zendesk::system/config/testconnection.phtml');
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
        $storeId = $this->helper->getStoreConfigScopeStoreId($this->getRequest());

        $zendeskConfigured = false;

        try {
            $this->apiHelper->tryValidateIsConfigured(
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
            $zendeskConfigured = true;
        } catch (\Exception $ex) {
            // zendesk integration must not be configured.
        }

        $originalData = $element->getOriginalData();
        $this->addData(
            [
                'button_label' => __($originalData['button_label']),
                'html_id' => $element->getHtmlId(),
                'ajax_url' => $this->_urlBuilder->getUrl('zendesk/system_config/testconnection'),
                'zendesk_configured' => $zendeskConfigured
            ]
        );

        return $this->_toHtml();
    }
}
