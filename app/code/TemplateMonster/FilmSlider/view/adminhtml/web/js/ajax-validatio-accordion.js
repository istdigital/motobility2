/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

define([
    'jquery',
    'mage/backend/validation'
],function($){
    'use strict';

    $.widget('tm.ajaxValidationAccordion', $.mage.validation,{

        _showErrors: function(data) {
            if(data.error && (data.error == "slider_error")) {
                var optionsAccordion = $('#slider_tabs_main_section_content');
                optionsAccordion.accordion("activate", 2);
                var validator = $("#edit_form").validate();
                validator.showErrors({
                    "startSlide": data.message
                });
            } else {
                return this._super(data);
            }
        },

    });

    return $.tm.ajaxValidationAccordion;
});