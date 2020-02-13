<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FilmSlider\Block\Adminhtml\Slider;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{

    /**
     * @var \Magento\Framework\Registry|null
     */
    protected $_coreRegistry = null;

    protected $_template = 'TemplateMonster_FilmSlider::widget/form/container.phtml';

    /**
     * Edit constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }


    protected function _construct()
    {
        $this->_objectId = 'slider_id';
        $this->_blockGroup = 'TemplateMonster_FilmSlider';
        $this->_controller = 'adminhtml_slider';

        $this->_formInitScripts[] = <<<'CUSTOMVALIDATION'
require([
    'jquery',
    'mage/backend/form',
    'mage/backend/validation'
], function($){

     var rules = {
        'validate-int-or-percentage': [
            function (v, element) {

                if(this.optional(element)) {
                    return true;
                }
                if(/^[0-9]*\.?[0-9]+%$/g.test(v)) {
                    return true;
                }
                var v = $.mage.parseNumber(v);
                if(!isNaN(v) && v >= 0) {
                    return true;
                }
                return false;
            },
            'Please specify value in integer or with percentage(%)'
        ],
        'validate-aspect-ratio': [
            function (value, element) {

                if(value == "-1") {return true;}

                return this.optional(element) ||  /^\d+(?:.\d{1,5})?$/.test(value);
            },
            'Permissible value: -1 < aspectRatio and aspectRatio < .00000'
        ],
        'validate-visible-size': [
            function (v) {
                if ($.mage.isEmptyNoTrim(v)) {
                    return true;
                }
                if(v == 'auto') {
                    return true;
                }
                v = $.mage.parseNumber(v);
                return !isNaN(v) && v >= 0;
            },
            'Permissible value: auto or int >= 0'
        ],
        'auto-scale-reference': [
            function (v,e) {
                if(this.optional(e)){return true;}
                v = $.mage.parseNumber(v);
                return !isNaN(v) && v >= -1;
            },
            'Permissible value: int >= -1'
        ]
    };

    $.each(rules, function (i, rule) {
        rule.unshift(i);
        $.validator.addMethod.apply($.validator, rule);
    });
});
CUSTOMVALIDATION;

        



        parent::_construct();

        if ($this->_isAllowedAction('TemplateMonster_FilmSlider::filmslider_save')) {
            $this->buttonList->update('save', 'label', __('Save Slider'));
            $this->buttonList->add(
                'saveandcontinue',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                        ],
                    ]
                ],
                -100
            );
        } else {
            $this->buttonList->remove('save');
        }
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('film_slider')->getId()) {
            return __("Edit Slider '%1'", $this->escapeHtml($this->_coreRegistry->registry('film_slider')->getTitle()));
        } else {
            return __('New Slider');
        }
    }

    /**
     * @param $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Getter of url for "Save and Continue" button
     * tab_id will be replaced by desired by JS later
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('filmslider/*/save', ['_current' => true, 'back' => 'edit', 'active_tab' => '{{tab_id}}']);
    }

    /**
     * Prepare layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('page_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'page_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'page_content');
                }
            };
        ";
        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getValidationUrl()
    {
        return $this->getUrl('filmslider/*/validate', ['_current' => true]);
    }
}
