<?php

namespace Zendesk\Zendesk\Block\Adminhtml\System\Config;

use Magento\Framework\Exception\NoSuchEntityException;

class RemoveIntegration extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Zendesk\Zendesk\Helper\Integration
     */
    protected $integrationHelper;
    /**
     * @var \Zendesk\Zendesk\Helper\ZendeskApp
     */
    protected $zendeskAppHelper;
    /**
     * @var \Zendesk\Zendesk\Helper\Api
     */
    protected $apiHelper;
    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    protected $storeRepository;
    /**
     * @var \Magento\Store\Api\WebsiteRepositoryInterface
     */
    protected $websiteRepository;

    /**
     * RemoveIntegration constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Zendesk\Zendesk\Helper\Integration $integrationHelper
     * @param \Zendesk\Zendesk\Helper\ZendeskApp $zendeskAppHelper
     * @param \Zendesk\Zendesk\Helper\Api $apiHelper
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     * @param \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        // end parent parameters
        \Zendesk\Zendesk\Helper\Integration $integrationHelper,
        \Zendesk\Zendesk\Helper\ZendeskApp $zendeskAppHelper,
        \Zendesk\Zendesk\Helper\Api $apiHelper,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository,
        // end custom parameters
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->integrationHelper = $integrationHelper;
        $this->zendeskAppHelper = $zendeskAppHelper;
        $this->apiHelper = $apiHelper;
        $this->storeRepository = $storeRepository;
        $this->websiteRepository = $websiteRepository;
    }

    /**
     * Set template
     *
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setTemplate('Zendesk_Zendesk::system/config/remove-integration.phtml');
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
        $this->addData(
            [
                'button_label' => __('Remove Zendesk Integration'),
                'html_id' => $element->getHtmlId(),
                'remove_url' => $this->_urlBuilder->getUrl('zendesk/system_config/removeIntegration')
            ]
        );

        return $this->_toHtml();
    }

    /**
     * Get if Zendesk API configured at given scope, glossing over exception
     *
     * @param $scopeType
     * @param null $scopeCode
     * @return bool
     */
    protected function isApiConfiguredAtScope(
        $scopeType = \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        try {
            $this->apiHelper->tryValidateIsConfigured($scopeType, $scopeCode);

            return true;
        } catch (\Exception $e) {
        }

        return false;
    }

    /**
     * Determine if button should or shouldn't display
     *
     * @return bool
     */
    public function displayButton()
    {
        $stores = $this->storeRepository->getList();
        $websites = $this->websiteRepository->getList();

        $shouldDisplay = false;

        // Check if integration present
        try {
            $this->integrationHelper->getIntegration();

            $shouldDisplay = true;
        } catch (NoSuchEntityException $e) {
        }

        // Check if API configured
        if ($this->isApiConfiguredAtScope()) { // check at default scope
            $shouldDisplay = true;
        }
        foreach ($websites as $website) { // check at website scope
            if ($this->isApiConfiguredAtScope(
                \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
                $website->getId()
            )) {
                $shouldDisplay = true;
            }
        }
        foreach ($stores as $store) { // check at store view scope
            if ($this->isApiConfiguredAtScope(
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store->getId()
            )) {
                $shouldDisplay = true;
            }
        }

        // Check if app installed
        if ($this->zendeskAppHelper->isZendeskAppInstalled()) { // check at default scope
            $shouldDisplay = true;
        }
        foreach ($websites as $website) { // check at website scope
            if ($this->zendeskAppHelper->isZendeskAppInstalled(
                \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
                $website->getId()
            )) {
                $shouldDisplay = true;
            }
        }
        foreach ($stores as $store) { // check at store view scope
            if ($this->zendeskAppHelper->isZendeskAppInstalled(
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store->getId()
            )) {
                $shouldDisplay = true;
            }
        }

        // Done!
        return $shouldDisplay;
    }
}
