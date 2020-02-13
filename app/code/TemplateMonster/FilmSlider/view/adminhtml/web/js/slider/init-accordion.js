/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

define([
    'jquery',
    'jquery/ui',
    'accordion'
],function($){
    'use strict';

    $.widget('TemplateMonster.sliderInitAccordion', {

        options: {
            header: "legend",
            content: "fieldset",
            collapsibleElement: "fieldset",
            multipleCollapsible: true,
            collapsible: true,
            animate: 200,
            active: false,
            heightStyle: "content"
        },

        _create: function() {
            this._addClass();
            $(this.element).accordion(this.options);
        },

        _addClass: function() {
            $('.addafter', this.element).addClass('note').css('display', 'block');
        }
    });

    return $.TemplateMonster.sliderInitAccordion;
  
});