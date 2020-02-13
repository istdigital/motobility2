<?php

namespace TemplateMonster\LayoutSwitcher\Block\Adminhtml\System\Config\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\View\Helper\Js as JsHelper;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Layout frontend model.
 *
 * @package TemplateMonster\LayoutSwitcher\Block\Adminhtml\System\Config\Field
 */
class Layout extends Field
{
    /**
     * @var JsHelper
     */
    protected $_jsHelper;

    /**
     * Livedemo flag.
     *
     * @var bool
     */
    protected $_livedemoMode;

    /**
     * Layout constructor.
     *
     * @param JsHelper $jsHelper
     * @param Context  $context
     * @param bool     $livedemoMode
     * @param array    $data
     */
    public function __construct(
        JsHelper $jsHelper,
        Context $context,
        $livedemoMode = false,
        array $data = []
    )
    {
        $this->_jsHelper = $jsHelper;
        $this->_livedemoMode = (bool) $livedemoMode;
        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    public function render(AbstractElement $element)
    {
//        if ($this->_livedemoMode) {
//            return '';
//        }

        return $this->_getExtraCss() . parent::render($element) . $this->_getExtraJs($element);
    }

    /**
     * @inheritdoc
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $html = parent::_getElementHtml($element);

        $html .= '<div class="preview">';
        foreach ($element->getValues() as $value) {
            $html .= sprintf(
                '<img id="%s" src="%s" alt="" style="display:none;" />',
                $value['value'],
                $this->_getPreviewImage($value['value'])
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
                .preview {
                    margin-top: 5px;
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
                        var image = '#' + $(this).val();
                        $(this).next('.preview').find('img').hide().filter(image).show();
                    }).change();
                });
            });
EOL;

        return $this->_jsHelper->getScript($output);
    }

    /**
     * Get preview image url.
     *
     * @param string $value
     *
     * @return string
     */
    protected function _getPreviewImage($value)
    {
        return $this->getViewFileUrl(sprintf(
            'TemplateMonster_LayoutSwitcher::images/previews/%s.png',
            $value
        ));
    }
}