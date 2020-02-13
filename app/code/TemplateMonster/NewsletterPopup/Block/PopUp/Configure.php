<?php

namespace TemplateMonster\NewsletterPopup\Block\PopUp;

use Magento\Cms\Model\Template\FilterProvider;
use TemplateMonster\NewsletterPopup\Helper\Data as PopupHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\View\Element\Template;

/**
 * Pop-Up configuration block.
 *
 * @package TemplateMonster\NewsletterPopup\Block
 */
class Configure extends Template
{
    /**
     * @var FilterProvider
     */
    protected $_filterProvider;

    /**
     * @var PopupHelper
     */
    protected $_popupHelper;

    /**
     * @var JsonHelper
     */
    protected $_jsonHelper;

    /**
     * @var string
     */
    protected $_template = 'popup/configure.phtml';

    /**
     * Configure constructor.
     *
     * @param FilterProvider   $filterProvider
     * @param PopupHelper      $popupHelper
     * @param JsonHelper       $jsonHelper
     * @param Template\Context $context
     * @param array            $data
     */
    public function __construct(
        FilterProvider $filterProvider,
        PopupHelper $popupHelper,
        JsonHelper $jsonHelper,
        Template\Context $context,
        array $data
    )
    {
        $this->_filterProvider = $filterProvider;
        $this->_popupHelper = $popupHelper;
        $this->_jsonHelper = $jsonHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get widget JSON configuration options.
     *
     * @return string
     */
    public function getWidgetConfigurationOptions()
    {
        return $this->_jsonHelper->jsonEncode(
            $this->getConfigurationOptions()
        );
    }

    /**
     * Get configuration options.
     *
     * @return array
     */
    public function getConfigurationOptions()
    {
        return [
            'customClass' => $this->_popupHelper->getCustomCssClass(),
            'timeout' => $this->_popupHelper->getPopupShowDelay(),
            'isShowOnStartup' => $this->_popupHelper->isShowOnStartup(),
            'isShowOnFooter' => $this->_popupHelper->isShowOnFooter(),
            'title' => $this->_popupHelper->getTitle(),
            'content' => $this->_filterBlock($this->_popupHelper->getContent()),
            'submit' => $this->_popupHelper->getSubmitText(),
            'cancel' => $this->_popupHelper->getCancelText(),
            'socialLinks' => $this->_getSocialLinks()
        ];
    }

    /**
     * Get social links.
     *
     * @return array
     */
    protected function _getSocialLinks()
    {
        $links = [];
        if ($this->_popupHelper->isSocialIcons()) {
            foreach ($this->_popupHelper->getAvailableIcons() as $icon) {
                $links[] = [
                    'type' => $icon,
                    'title' => ucfirst($icon),
                    'href' => $this->_popupHelper->getIconLink($icon) ?: 'javascript:void(0)',
                ];
            }
        }

        return $links;
    }

    /**
     * Filter block content.
     *
     * @param string $content
     *
     * @return string
     * @throws \Exception
     */
    protected function _filterBlock($content)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $blockFilter = $this->_filterProvider->getBlockFilter();
        $blockFilter->setStoreId($storeId);

        return $blockFilter->filter($content);
    }
}