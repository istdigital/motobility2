/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */


define([
    'jquery',
    'mage/backend/button',
],function($){
    'use strict';

    /**
     * Widget trigger event on insert image to canvas
     *
     */

    $.widget('tm.sliderSaveButton', $.ui.button,{

        _click: function() {
            $(this.element).trigger('beforeSaveEvent');
            this._super();
        }
    });

    return $.tm.sliderSaveButton;
});