<?php

namespace Zendesk\Zendesk\Block\Adminhtml\System\Config;

class ZendeskAppStatus extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Zendesk\Zendesk\Helper\ZendeskApp
     */
    protected $zendeskAppHelper;
    /**
     * @var \Zendesk\Zendesk\Helper\Api
     */
    protected $apiHelper;
    /**
     * @var \Zendesk\Zendesk\Helper\Data
     */
    protected $helper;

    /**
     * ZendeskApp constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Zendesk\Zendesk\Helper\ZendeskApp $zendeskAppHelper
     * @param \Zendesk\Zendesk\Helper\Api $apiHelper
     * @param \Zendesk\Zendesk\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        // end parent parameters
        \Zendesk\Zendesk\Helper\ZendeskApp $zendeskAppHelper,
        \Zendesk\Zendesk\Helper\Api $apiHelper,
        \Zendesk\Zendesk\Helper\Data $helper,
        // end custom parameters
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->zendeskAppHelper = $zendeskAppHelper;
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
        $this->setTemplate('Zendesk_Zendesk::system/config/zendesk-app-status.phtml');
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
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $storeId = $this->helper->getStoreConfigScopeStoreId($this->getRequest());

        $zendeskConfigured = false;
        try {
            $this->apiHelper->tryValidateIsConfigured(\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
            $zendeskConfigured = true;
        } catch (\Exception $ex) {
            // Zendesk must not yet be configured to successfully authenticate.
        }

        $authenticationConfirmed = false;

        try {
            $this->apiHelper->tryAuthenticate(\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
            $authenticationConfirmed = true;
        } catch (\Exception $ex) {
            // Zendesk must not yet be configured to successfully authenticate.
        }

        $statusMessage = __('Status unknown.');
        $isInstalled = false; // default value

        if (!$zendeskConfigured) {
            $statusMessage = __('Zendesk API credentials not configured.');
        } elseif (!$authenticationConfirmed) {
            $statusMessage = __('Invalid API credentials.');
        } else {
            $isInstalled = $this->zendeskAppHelper->isZendeskAppInstalled(
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );

            $statusMessage = $isInstalled ? __('Successfully Installed.') : __('Not Installed.');
        }

        $this->addData(
            [
                'zendesk_configured' => $zendeskConfigured,
                'authentication_confirmed' => $authenticationConfirmed,
                'is_installed' => $isInstalled,
                'button_label' => $statusMessage,
                'html_id' => $element->getHtmlId()
            ]
        );

        return $this->_toHtml();
    }
}
