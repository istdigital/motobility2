<?php

namespace TemplateMonster\SiteMaintenance\Block\Adminhtml\System\Config\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\View\Helper\Js as JsHelper;

/**
 * ColorPicker frontend model
 *
 * @package TemplateMonster\SiteMaintenance\Block\Adminhtml\System\Config\Field
 */
class ColorPicker extends Field
{
    /**
     * @var JsHelper
     */
    protected $jsHelper;

    /**
     * ColorPicker constructor.
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
        $element->addClass('field-color-picker');
        $html = parent::render($element);

        return $this->_getExtraCss() . $html . $this->_getExtraJs($element);
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
            require(['jquery', 'jquery/colorpicker/js/colorpicker'], function($) {
                $(function() {
                    var colorPicker = $('#{$element->getHtmlId()}')
                        .attr('autocomplete', 'off')
                        .css('backgroundColor', function() {
                            return $(this).val();
                        })
                    ;

                    colorPicker.ColorPicker({
                        onChange: function (hsb, hex, rgb) {
                            var color = '#' + hex;
                            colorPicker.val(color).css('backgroundColor', color);
                        }
                    });

                    //colorPicker.keypress(false);
                });
            });
EOL;

        return $this->jsHelper->getScript($output);
    }

    /**
     * Get extra CSS
     *
     * @return string
     */
    protected function _getExtraCss()
    {
        return <<<EOL
            <style>
                .field-color-picker {
                    cursor: pointer;
                    background: url({$this->getViewFileUrl('TemplateMonster_SiteMaintenance::images/colorpicker.png')})
                    no-repeat right;
                }
                .field-color-picker::-ms-clear {
                    display: none;
                }
            </style>
EOL;
    }
}