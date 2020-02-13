<?php

namespace TemplateMonster\AjaxWishlist\Block;

use TemplateMonster\AjaxWishlist\Helper\Data as AjaxWishlistHelper;
use Magento\Customer\Model\Url;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\View\Element\Template;

/**
 * AjaxWishlist Configure block
 *
 * @package TemplateMonster\AjaxWishlist\Block
 */
class Configure extends Template
{
    /**
     * @var AjaxWishlistHelper
     */
    protected $ajaxWishlistHelper;

    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * Configure constructor.
     *
     * @param AjaxWishlistHelper $ajaxWishlistHelper
     * @param JsonHelper         $jsonHelper
     * @param Template\Context   $context
     * @param array              $data
     */
    public function __construct(
        AjaxWishlistHelper $ajaxWishlistHelper,
        JsonHelper $jsonHelper,
        Template\Context $context,
        array $data
    )
    {
        $this->ajaxWishlistHelper = $ajaxWishlistHelper;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get JSON configuration option
     *
     * @return string
     */
    public function getWidgetConfigurationOptions()
    {
        return $this->jsonHelper->jsonEncode(
            $this->getConfigurationOptions()
        );
    }

    /**
     * Get configuration option
     *
     * @return array
     */
    public function getConfigurationOptions()
    {
        return [
            'isShowSpinner' => $this->ajaxWishlistHelper->isShowSpinner(),
            'isShowSuccessMessage' => $this->ajaxWishlistHelper->isShowSuccessMessage(),
            'successMessageText' => $this->ajaxWishlistHelper->getSuccessMessageText(),
            'customerLoginUrl' => $this->_urlBuilder->getUrl(Url::ROUTE_ACCOUNT_LOGIN)
        ];
    }
}