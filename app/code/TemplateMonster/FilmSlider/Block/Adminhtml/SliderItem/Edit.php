<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FilmSlider\Block\Adminhtml\SliderItem;

use Magento\Backend\Block\Widget\Form\Container;

class Edit extends Container
{
    /**
     * @var \Magento\Framework\Registry|null
     */
    protected $_coreRegistry = null;

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
                if(!isNaN(v)) {
                    return true;
                }
                return false;
            },
            'Please specify value in integer or with percentage(%)'
        ]
    };

    $.each(rules, function (i, rule) {
        rule.unshift(i);
        $.validator.addMethod.apply($.validator, rule);
    });
});
CUSTOMVALIDATION;

        parent::__construct($context, $data);
    }


    protected function _construct()
    {
        $this->_objectId = 'slideritem_id';
        $this->_blockGroup = 'TemplateMonster_FilmSlider';
        $this->_controller = 'adminhtml_sliderItem';

        parent::_construct();

        if ($this->_isAllowedAction('TemplateMonster_FilmSlider::filmslider_save')) {
            $this->buttonList->remove('save');
            $this->addButton(
                'save',
                [
                    'label' => __('Slider Item Save'),
                    'class' => 'save primary',
                    'data_attribute' => [
                        'mage-init' => [
                            'sliderSaveButton' => ['event' => 'save', 'target' => '#edit_form']
                        ],
                    ]
                ],
                1
            );
            //$this->buttonList->update('save', 'label', __('Slider Item Save'));

            $this->buttonList->add(
                'saveandcontinue',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'sliderSaveButton' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
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
            return __("Edit Slider Item '%1'", $this->escapeHtml($this->_coreRegistry->registry('film_slider')->getTitle()));
        } else {
            return __('New Slider Item');
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
}
