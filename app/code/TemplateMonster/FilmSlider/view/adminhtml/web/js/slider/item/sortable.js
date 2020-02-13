/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

define([
    'jquery',
    'TemplateMonster_FilmSlider/js/slider/item/sliderpagedit',
    'TemplateMonster_FilmSlider/js/slider/item/canvas',
],function($){
'use strict';

    /**
     * Make ul sortable
     */
    $.widget('tm.sliderItemLayerSortable', $.tm.sliderPageEdit,{

        options: {
            sortableItemId: 'data-id',
            sortableOptions: {}
        },

        _create: function() {
            $(this.element).sortable(this.options.sortableOptions);
            $(this.element).on("sortupdate", $.proxy(function( event, ui ) {
                var arr = $(this.element).sortable('toArray',{attribute:this.options.sortableItemId});
                this._setLayersOrder(event, ui,arr);
            },this));

            $(this.element).on("click", "li", $.proxy(function(event){
                this._selectLayerItem(event);
                if($(event.target).data('type') == 'text') {
                    this._textSettingFieldUnable();
                } else {
                    this._textSettingFieldDisable();
                }
            },this));


            $(this.options.imageCanvasBlock).on("click","div",$.proxy(function(event){
                if($(event.target).data('type') == 'text') {
                    this._textSettingFieldUnable();
                } else {
                    this._textSettingFieldDisable();
                }
            },this));
        },

        _setLayersOrder: function(event, ui,arr){
            this._changeSortableItemOrder(arr);
            this._changeLayoutImageZindex(arr);
        },

    });

    return $.tm.sliderItemLayerSortable;
});