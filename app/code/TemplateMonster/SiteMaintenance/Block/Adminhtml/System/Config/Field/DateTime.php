<?php

namespace TemplateMonster\SiteMaintenance\Block\Adminhtml\System\Config\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\View\Helper\Js as JsHelper;

/**
 * DateTime frontend model
 *
 * @package TemplateMonster\SiteMaintenance\Block\Adminhtml\System\Config\Field
 */
class DateTime extends Field
{
    /**
     * @var JsHelper
     */
    protected $jsHelper;

    /**
     * DateTime constructor.
     *
     * @param JsHelper $jsHelper
     * @param Context  $context
     * @param array    $data
     */
    public function __construct(JsHelper $jsHelper, Context $context, array $data = [])
    {
        $this->jsHelper = $jsHelper;

        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    public function render(AbstractElement $element)
    {
        $element->addClass('datepicker');
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
            require(['jquery', 'mage/calendar'], function($) {
                $(function() {
                    var datePicker = $('#{$element->getHtmlId()}');

                    datePicker.calendar({
                        dateFormat: "yy-M-dd",
                        showsTime: true,
                        timeFormat: "HH:mm:ss",
                        sideBySide: true,
                        closeText: "Done",
                        selectOtherMonths: true,
                    });
                });
            });
EOL;

        return $this->jsHelper->getScript($output);
    }
}