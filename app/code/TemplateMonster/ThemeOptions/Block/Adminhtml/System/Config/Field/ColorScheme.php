<?php

namespace TemplateMonster\ThemeOptions\Block\Adminhtml\System\Config\Field;

use TemplateMonster\ThemeOptions\Helper\ColorScheme as ColorSchemeHelper;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\View\Helper\Js as JsHelper;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Color scheme frontend model.
 *
 * @package TemplateMonster\ThemeOptions\Block\Adminhtml\System\Config\Field
 */
class ColorScheme extends Field
{
    /**
     * @var ColorSchemeHelper
     */
    protected $_colorSchemeHelper;

    /**
     * @var JsHelper
     */
    protected $_jsHelper;

    /**
     * ColorScheme constructor.
     *
     * @param ColorSchemeHelper $colorSchemeHelper
     * @param JsHelper          $jsHelper
     * @param Context           $context
     * @param array             $data
     */
    public function __construct(
        ColorSchemeHelper $colorSchemeHelper,
        JsHelper $jsHelper,
        Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\Store $storeModel,
        array $data = []
    )
    {
        $this->_colorSchemeHelper = $colorSchemeHelper;
        $this->_jsHelper = $jsHelper;
        $this->_request = $request;
        $this->_storeModel = $storeModel;
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
        // TODO Colors look like swatches
//        $html = '';
//
//        foreach ((array) $element->getValues() as $id => $data) {
//            $html .= sprintf(
//                '<div class="color-scheme" data-id="%s" data-label="%s" data-color="%s"></div>',
//                $id,
//                $data['label'],
//                $data['color']
//            );
//        }

        $name = $this->_getWebsiteCode();
        $oldValues = $element->getValues();
        $newValues = isset($oldValues[$name]) ? $oldValues[$name] : [];
        $element->setValues($newValues);

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
                .color-scheme {
                    /*width: 30px;*/
                    padding: 1px 2px;
                    min-width: 30px;
                    max-width: 90px;
                    height: 20px;
                    float: left;
                    margin: 0 10px 5px 0;
                    text-align: center;
                    cursor: pointer;
                    position: relative;
                    border: 1px solid rgb(218, 218, 218);
                    overflow: hidden;
                    text-overflow: ellipsis;
                    clear: right;
                }

                .color-scheme.selected {
                    outline: 2px solid #FF5100;
                    border: 1px solid #fff;
                    color: #333;
                }

                .color-scheme:not(.selected):hover {
                    outline: 1px solid #999;
                    border: 1px solid #fff;
                    color: #333;
                }
                
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
        $storeId = $this->_getStoreId();

        return <<<EOL
            <script type="text/x-magento-init">
                {
                    "#{$element->getHtmlId()}": {
                        "colorScheme": {
                            "defaultValues": {$this->_colorSchemeHelper->getJsonDefaultValues($storeId)},
                            "userValues": {$this->_colorSchemeHelper->getJsonUserValues($storeId)}
                        }
                    }
                }
            </script>
EOL;
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
            'TemplateMonster_ThemeOptions::images/previews/%s/%s.png',
            $this->_getWebsiteCode(),
            $value
        ));
    }

    /**
     * Get form block.
     *
     * @return bool|\Magento\Config\Block\System\Config\Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getFormBlock()
    {
        /** @var \Magento\Framework\View\Element\AbstractBlock $edit */
        $edit = $this->getLayout()->getBlock('system.config.edit');
        $form = $edit->getChildBlock('form');

        return $form;
    }

    /**
     * Get store view id.
     *
     * @return string
     */
    protected function _getStoreId()
    {
        //$this->_getFormBlock()->getStoreCode()

        if($this->_getRequestWebsiteId()){
            $collection = $this->_storeModel->getCollection()->addWebsiteFilter($this->_getRequestWebsiteId())
                ->getData();
            $storeId = $collection[0]['store_id'];
        }

        if($this->_getRequestStoreId()){
            $storeId = $this->_getRequestStoreId();
        }

        return $storeId;
    }

    /**
     * Get website code.
     *
     * @return string
     */
    protected function _getWebsiteCode()
    {
        $websiteCode = null;

        if($this->_getRequestStoreId()){
            $store = $this->_storeManager->getStore($this->_getRequestStoreId());
            $websiteId = $store->getWebsiteId();
            $websiteCode = $this->_storeManager->getWebsite($websiteId)->getCode();
        }

        if($this->_getRequestWebsiteId()){
            $websiteId = $this->_getRequestWebsiteId();
            $websiteCode = $this->_storeManager->getWebsite($websiteId)->getCode();
        }

        return $websiteCode;

//        $storeId = $this->_getStoreId();
//        /** @var \Magento\Store\Model\Store $store */
//        $store = $this->_storeManager->getStore($this->_getStoreId());
//        $code = $store->getWebsite()->getCode();
//        return $code;
    }

    protected function _getRequestWebsiteId()
    {
        return !null == $this->_request->getParam('website') ? $this->_request->getParam('website') : false;
    }

    protected function _getRequestStoreId()
    {
        return !null == $this->_request->getParam('store') ? $this->_request->getParam('store') : false;
    }
}