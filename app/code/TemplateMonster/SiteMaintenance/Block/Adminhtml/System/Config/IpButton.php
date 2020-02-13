<?php

namespace TemplateMonster\SiteMaintenance\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\View\Helper\Js as JsHelper;
use TemplateMonster\SiteMaintenance\Helper\Data as HelperData;

/**
 * DateTime frontend model
 *
 * @package TemplateMonster\SiteMaintenance\Block\Adminhtml\System\Config\Field
 */
class IpButton extends Field
{
    const TEXTAREA_ID = 'site_maintenance_general_whitelist';

    const ALREADY_IN_WHITELIST_MESSAGE = 'Your IP is already in whitelist.';

    /**
     * @var Data
     */
    protected $jsHelper;

    /**
     * @var JsHelper
     */
    protected $_helper;

    /**
     * DateTime constructor.
     *
     * @param JsHelper $jsHelper
     * @param Context  $context
     * @param HelperData $helper
     * @param array    $data
     */
    public function __construct(
        JsHelper $jsHelper,
        Context $context,
        HelperData $helper,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->jsHelper = $jsHelper;

        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    public function render(AbstractElement $element)
    {
        $element->addClass('ipbutton');
        $element->setValue('Add');
        $html = parent::render($element);

        return $html . $this->_getExtraJs($element);
    }

    /**
     * Get extra JS
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _getExtraJs(AbstractElement $element)
    {
        $output = <<<EOL
            require(['jquery'], function($) {
                $(function() {
                    var element = $('#{$element->getHtmlId()}');
                    var currentIp = '{$this->getClientIp()}';
                    var textarea = $('#{$this->getTextAreaId()}');
                    element.click(function() {
                        if (textarea.val().includes(currentIp)) {
                            alert('{$this->getAlreadyInWhitelistMessage()}');
                        } else if (!textarea.val()) {
                            textarea.val(currentIp);
                        } else {
                            var value = textarea.val();
                            textarea.val(value + ', ' + currentIp);
                        }
                    });
                });
            });
EOL;

        return $this->jsHelper->getScript($output);
    }

    public function getAlreadyInWhitelistMessage()
    {
        return self::ALREADY_IN_WHITELIST_MESSAGE;
    }

    public function getTextAreaId()
    {
        return self::TEXTAREA_ID;
    }

    public function getClientIp()
    {
        return $this->_helper->getClientIp();
    }
}