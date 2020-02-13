<?php

namespace TemplateMonster\LayoutSwitcher\Block\Adminhtml\System\Config\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\View\Helper\Js as JsHelper;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * ColorScheme frontend model.
 *
 * @package TemplateMonster\LayoutSwitcher\Block\Adminhtml\System\Config\Field
 */
class ColorScheme extends Field
{
    /**
     * @var JsHelper
     */
    protected $_jsHelper;

    /**
     * Layout constructor.
     *
     * @param JsHelper $jsHelper
     * @param Context  $context
     * @param array    $data
     */
    public function __construct(
        JsHelper $jsHelper,
        Context $context,
        array $data = []
    )
    {
        $this->_jsHelper = $jsHelper;
        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    public function render(AbstractElement $element)
    {
        $html = parent::render($element);

        return $this->_getExtraCss() . $html . $this->_getExtraJs($element);
    }

    /**
     * @inheritdoc
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $html = parent::_getElementHtml($element);

        $html .= '<div class="color-scheme">';
        foreach ($element->getValues() as $value) {
            $html .= sprintf(
                '<div id="%s" style="display:none; background-color:%s;"></div>',
                $value['value'],
                $value['color']
            );
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * Get extra CSS.
     *
     * @return string
     */
    protected function _getExtraCss()
    {
        return <<<EOL
            <style>
                .color-scheme {
                    margin-top: 5px;
                }
                .color-scheme div {
                    width: 100px;
                    height: 100px;
                }
            </style>
EOL;
    }

    /**
     * Get extra javascript.
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
                    $('#{$element->getHtmlId()}').change(function() {
                        var filler = '#' + $(this).val();
                        $(this).next('.color-scheme').find('div').hide().filter(filler).show();
                    }).change();
                });
            });
EOL;

        return $this->_jsHelper->getScript($output);
    }
}