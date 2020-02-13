<?php

namespace TemplateMonster\SocialSharing\Block;

use TemplateMonster\SocialSharing\Helper\Data as SocialSharingHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\View\Element\Template;

/**
 * SocialSharing AddThisInit Block.
 */
class AddThisInit extends Template
{
    /**
     * @var string
     */
    protected $_template = 'addthis_init.phtml';

    /**
     * @var SocialSharingHelper
     */
    protected $_helper;

    /**
     * @var JsonHelper
     */
    protected $_jsonHelper;

    /**
     * Init constructor.
     *
     * @param SocialSharingHelper $helper
     * @param JsonHelper          $jsonHelper
     * @param Template\Context    $context
     * @param array               $data
     */
    public function __construct(
        SocialSharingHelper $helper,
        JsonHelper $jsonHelper,
        Template\Context $context,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->_jsonHelper = $jsonHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get AddThis profile identifier.
     *
     * @return string
     */
    public function getProfileId()
    {
        return $this->_helper->getProfileId();
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
            'services_exclude' => $this->_helper->getExcludedServices(),
            'services_compact' => $this->_helper->getCompactMenuServices(),
            'services_expanded' => $this->_helper->getExpandedMenuServices(),
            'services_custom' => [$this->_helper->getCustomService()],
            'ui_click' => !$this->_helper->isCompactMenuHover(),
            'ui_delay' => $this->_helper->getDelay(),
            'ui_hover_direction' => $this->_helper->getCompactMenuDirection(),
            'ui_language' => $this->_helper->getMenuLanguage(),
            'ui_offset_top' => $this->_helper->getOffsetTop(),
            'ui_offset_left' => $this->_helper->getOffsetLeft(),
            'ui_use_css' => $this->_helper->isLoadAddthisCss(),
            'ui_508_compliant' => $this->_helper->isNewWindow(),
            'data_track_clickback' => $this->_helper->isTrackClickbacks(),
            'data_ga_tracker' => $this->_helper->getGoogleAnalyticsId(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if (!$this->_helper->isEnabled()) {
            return '';
        }

        return parent::_toHtml();
    }
}
