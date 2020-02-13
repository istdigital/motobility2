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
    'TemplateMonster_FilmSlider/js/slider/item/layer/image',
    'TemplateMonster_FilmSlider/js/slider/item/layer/items',
],function($) {
    'use strict';

    $.widget('tm.sliderItemLayerImageUpdate', $.tm.sliderPageEdit,{

        _create: function() {
            //Observ input if insert file name set to background.
            this._on({
                change: function(event)
                {
                    var imageSrc = $(event.target).val();
                    var itemId = this._getLayerItemId();
                    var fullImageSrc = this._getFullImagePath(imageSrc);
                    var layerData = this._getLayoutData(itemId);

                    if(layerData && imageSrc) {
                        layerData.layer_images = imageSrc;
                        this._setLayoutData(itemId,layerData);
                        $(this.options.sliderLayerImage).val(imageSrc);

                        $(this.options.imageCanvasBlock)
                            .find('div[data-id="'+itemId+'"]')
                            .css("background-image" , 'url(' + fullImageSrc + ')');
                    }
                }
            });

            //on init check if item enable:

            var canvasSortableItems = this._getCanvasSortableItemsSelector();
            if($(canvasSortableItems).find('div[class="selected"],li[class="selected"]').data('type') != 'text') {
                if ($(canvasSortableItems).find('div[data-id],li[data-id]').hasClass("selected")) {
                    this._makeButtonEnable(this.options.sliderLayerImageUpdate);
                }
            }

            $(this._getCanvasSortableItemsSelector()).on('click','li,div',$.proxy(function(event){
                var type = $(event.target).data('type');
                if(type == 'text') {
                    this._makeButtonDisable(this.options.sliderLayerImageUpdate);
                } else {
                    this._makeButtonEnable(this.options.sliderLayerImageUpdate);
                }
            },this));

            $(canvasSortableItems).on(this.options.eventLayerItemClicked,
                $.proxy(function(event,eventElement){
                    var type = $(event.target).data('type');
                    if(type == 'text') {
                        this._makeButtonDisable(this.options.sliderLayerImageUpdate);
                    } else {
                        this._makeButtonEnable(this.options.sliderLayerImageUpdate);
                    }
                },this));

            $(canvasSortableItems).on(this.options.eventLayerItemManyRemoved,$.proxy(function(event){
                this._makeButtonDisable(this.options.sliderLayerImageUpdate);
            },this));

            $(canvasSortableItems).on(this.options.eventLayerItemRemoved,$.proxy(function(event){
                if($(canvasSortableItems).find('div[data-id],li[data-id]').hasClass("selected") &&
                    ($(canvasSortableItems).find('div[data-id],li[data-id]').data('type') != 'text')
                ) {
                    this._makeButtonEnable(this.options.sliderLayerImageUpdate);
                } else {
                    this._makeButtonDisable(this.options.sliderLayerImageUpdate);
                }
            },this));
        },

    });

    return $.tm.sliderItemLayerImageUpdate;
});