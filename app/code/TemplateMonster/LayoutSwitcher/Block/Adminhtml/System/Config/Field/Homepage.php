<?php

namespace TemplateMonster\LayoutSwitcher\Block\Adminhtml\System\Config\Field;

use TemplateMonster\LayoutSwitcher\Model\Config\Source\Website as WebsiteSource;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\View\Helper\Js as JsHelper;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Homepage frontend model.
 *
 * @package TemplateMonster\LayoutSwitcher\Block\Adminhtml\System\Config\Field
 */
class Homepage extends Layout
{
    /**
     * @var WebsiteSource
     */
    protected $_websiteSource;

    /**
     * Homepage constructor.
     *
     * @param WebsiteSource $websiteSource
     * @param JsHelper      $jsHelper
     * @param Context       $context
     * @param bool          $livedemoMode
     * @param array         $data
     */
    public function __construct(
        WebsiteSource $websiteSource,
        JsHelper $jsHelper,
        Context $context,
        $livedemoMode = false,
        array $data = []
    ) {
        $this->_websiteSource = $websiteSource;
        parent::__construct($jsHelper, $context, $livedemoMode, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _getExtraJs(AbstractElement $element)
    {
        $output = <<<EOL
            require(['jquery', 'underscore'], function($, _) {
                $(function() {
                    var map = {$this->_getThemeToHomepageJson($element)};
                    var element = $('#{$element->getHtmlId()}');
                    var homepage = element.clone(true);
                    var wrapper = element.parent();
                    $('#layout_switcher_general_default_theme').change(function() {
                        var theme = $(this).val();
                        $(wrapper).find('select').remove().end().html(homepage.clone(true)).find('option').filter(function() {
                            if (!(theme in map))
                                return false;

                            return map[theme].indexOf($(this).val()) === -1;
                        }).remove();
                    }).change();
                });
            });
EOL;

        return parent::_getExtraJs($element) . $this->_jsHelper->getScript($output);
    }

    /**
     * Get theme to homepage map json.
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _getThemeToHomepageJson(AbstractElement $element)
    {
        return json_encode($this->_getThemeToHomepage($element));
    }

    /**
     * Get theme to homepage map.
     *
     * @param AbstractElement $element
     *
     * @return array
     */
    protected function _getThemeToHomepage(AbstractElement $element)
    {
        $map = [];
        foreach ($element->getValues() as $value) {
            $code = $this->_getWebsiteCode($value['website_id']);
            $store = $value['value'];
            if (!isset($map[$code])) {
                $map[$code] = [];
            }
            $map[$code][] = $store;
        }

        return $map;
    }

    /**
     * Get wbsite code.
     *
     * @param int $websiteId
     *
     * @return null|string
     */
    protected function _getWebsiteCode($websiteId)
    {
        $options = $this->_websiteSource->toOptionArray();

        foreach ($options as $option) {
            if ($option['website_id'] === $websiteId) {
                return $option['value'];
            }
        }

        return null;
    }
}