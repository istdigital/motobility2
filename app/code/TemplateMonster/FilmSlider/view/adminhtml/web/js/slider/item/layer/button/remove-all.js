/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */


define([
    'jquery',
    'mage/translate',
    'Magento_Ui/js/modal/confirm',
    'TemplateMonster_FilmSlider/js/slider/item/sliderpagedit',
    'TemplateMonster_FilmSlider/js/slider/item/canvas',
    'TemplateMonster_FilmSlider/js/slider/item/layer/image',


],function($,$t,confirm){
    'use strict';
    $.widget('tm.sliderItemLayerButtonRemoveAll', $.tm.sliderPageEdit, {

        _create: function(){

            this._on({
                click: $.proxy(function(event)
                {
                    confirm({
                        content: $t('Do you want to delete all layers?'),
                        actions: {
                            confirm: $.proxy(function () {
                                $(this.options.imageCanvasBlock).html('');
                                this._makeButtonDisable(this.element);
                            },this),
                        }
                    });
                },this)
            });

            //on init check if item enable:
            var canvasSortableItems = this._getCanvasSortableItemsSelector();
            if($(canvasSortableItems).find('div[data-id],li[data-id]').hasClass("selected")) {
                this._makeButtonEnable(this.element);
            }

            $(canvasSortableItems).on(this.options.eventLayerItemClicked,
                $.proxy(function(event,eventElement){
                    this._makeButtonEnable(this.element);
                },this));

            $(canvasSortableItems).on(this.options.eventLayerItemManyRemoved,$.proxy(function(event){
                this._makeButtonDisable(this.element);
            },this));

            $(canvasSortableItems).on(this.options.eventLayerItemRemoved,$.proxy(function(event){
                if(!$(canvasSortableItems).find('div[data-id]').length) {
                    this._makeButtonDisable(this.element);
                }
            },this));

        },

    });
    return $.tm.sliderItemLayerButtonRemoveAll;
});