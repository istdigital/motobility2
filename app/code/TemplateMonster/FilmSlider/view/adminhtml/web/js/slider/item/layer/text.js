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

    $.widget('tm.sliderItemLayerText', $.tm.sliderPageEdit,{

        options: {
            layerTextHtml : '#slider_layer_text_html',
            layerTextWidth : '#slider_layer_text_width',
            layerTextHeight : '#slider_layer_text_height',
            layerTextBackgroundColor : '#slider_layer_text_back_color',
            layerTextBackgroundOpacity : '#slider_layer_text_back_opacity',
            layerTextColor : '#slider_layer_text_color',
            layerTextFontSize: '#slider_layer_text_font_size',
            layerTextLineHeight: '#slider_layer_text_line_height',
            layerTextFontStyle: '#slider_layer_text_font_style',
            layerTextFontWeight: '#slider_layer_text_font_weight',
            layerTextFontFamily: '#slider_layer_text_font_family',
            layerTextFontIndent: '#slider_layer_text_indent',
            layerTextHtmlType: 'text',
        },

        _create: function() {

            $(this.element).on('click',$.proxy(function(event){
                if(0 < this._getLayerItemId()){
                    this._setDataFromForm(this._getLayerItemId(),this._getItemsLayoutAndAnimationSelector());
                }
            },this));

            $(this.options.layerTextHtml).on('change',$.proxy(function(event){
                var textHtml = $(event.target).val();
                var textHtmlDiv = this._getLayerItemSelected(this.options.layerTextHtmlType);
                var textHtmlLi = this._getLayerItemSelectedLi(this.options.layerTextHtmlType);

                if(textHtmlDiv){textHtmlDiv.html(textHtml);}
            },this));

            var canvasSortableItems = this._getCanvasSortableItemsSelector();
            $(canvasSortableItems).on(this.options.eventLayerItemManyRemoved,$.proxy(function(event){
                this._textSettingFieldDisable();
            },this));

            $(canvasSortableItems).on(this.options.eventLayerItemRemoved,$.proxy(function(event){
                if(!$(canvasSortableItems).find('div[data-id]').length) {
                    this._textSettingFieldDisable();
                }
            },this));

            $(this.options.layerTextWidth).on('change',$.proxy(function(event){
               var textWidth = $(event.target).val();
               var textHtmlDiv = this._getLayerItemSelected(this.options.layerTextHtmlType);
               if(textHtmlDiv){textHtmlDiv.css('width',textWidth+'px');}
            },this));

            $(this.options.layerTextHeight).on('change',$.proxy(function(event){
                var textHeight = $(event.target).val();
                var textHtmlDiv = this._getLayerItemSelected(this.options.layerTextHtmlType);
                if(textHtmlDiv){textHtmlDiv.css('height',textHeight+'px');}
            },this));

            $(this.options.layerTextBackgroundColor).on('change',$.proxy(function(event){
                var textBackgroundColor = $(event.target).val();
                var textHtmlDiv = this._getLayerItemSelected(this.options.layerTextHtmlType);
                $(event.target).css('background-color',textBackgroundColor);
                if(textHtmlDiv){textHtmlDiv.css('background-color',textBackgroundColor);}
            },this));

            $(this.options.layerTextBackgroundOpacity).on('change',$.proxy(function(event){
                var textBackgroundOpacity = $(event.target).val();
                var textHtmlDiv = this._getLayerItemSelected(this.options.layerTextHtmlType);
                if(textHtmlDiv){textHtmlDiv.css('opacity',textBackgroundOpacity);}
            },this));

            $(this.options.layerTextColor).on('change',$.proxy(function(event){
                var textColor = $(event.target).val();
                var textHtmlDiv = this._getLayerItemSelected(this.options.layerTextHtmlType);
                $(event.target).css('background-color',textColor);
                if(textHtmlDiv){textHtmlDiv.css('color',textColor);}
            },this));

            $(this.options.layerTextFontSize).on('change',$.proxy(function(event){
                var textFontSize = $(event.target).val();
                var textHtmlDiv = this._getLayerItemSelected(this.options.layerTextHtmlType);
                if(textHtmlDiv){textHtmlDiv.css('font-size',textFontSize+'px');}
            },this));

            $(this.options.layerTextLineHeight).on('change',$.proxy(function(event){
                var textLineHeight = $(event.target).val();
                var textHtmlDiv = this._getLayerItemSelected(this.options.layerTextHtmlType);
                if(textHtmlDiv){textHtmlDiv.css('line-height',textLineHeight+'px');}
            },this));

            $(this.options.layerTextFontStyle).on('change',$.proxy(function(event){
                var textFontStyle = $(event.target).val();
                var textHtmlDiv = this._getLayerItemSelected(this.options.layerTextHtmlType);
                if(textHtmlDiv){textHtmlDiv.css('font-style',textFontStyle);}
            },this));

            $(this.options.layerTextFontWeight).on('change',$.proxy(function(event){
                var textFontWeight = $(event.target).val();
                var textHtmlDiv = this._getLayerItemSelected(this.options.layerTextHtmlType);
                if(textHtmlDiv){textHtmlDiv.css('font-weight',textFontWeight);}
            },this));


            $(this.options.layerTextFontFamily).on('change',$.proxy(function(event){
                var textFontFamily = $(event.target).val();
                var textHtmlDiv = this._getLayerItemSelected(this.options.layerTextHtmlType);
                if(textHtmlDiv){textHtmlDiv.css('font-family',textFontFamily);}
            },this));

            $(this.options.layerTextFontIndent).on('change',$.proxy(function(event){
                var textFontIndent = $(event.target).val();
                var textHtmlDiv = this._getLayerItemSelected(this.options.layerTextHtmlType);
                if(textHtmlDiv){textHtmlDiv.css('text-indent',textFontIndent);}
            },this));

            //Observ input if insert file name set to background.
            this._on({
                click: function(event)
                {

                    var layerNumber = this._getLayerNumber();
                    this._incrementLayerNumber();
                    this._setLayerItemId(layerNumber);
                    this._resetItemLayoutForm('text');
                    var itemData = {sortOrder: layerNumber};
                    this._setLayoutData(layerNumber,itemData);
                    var div = this._createTextDiv(layerNumber,'Layer-text-' + layerNumber);
                    this._addImageToCanvas(div);
                    var li = this._createIlSortableItemText(layerNumber,itemData);
                    this._addItemToSortable(li);
                    $(this.options.imageCanvasBlock).find('div[data-id="'+layerNumber+'"]').trigger('click');
                    $(this.options.layerTextHtml).val('Layer-text-' + layerNumber);
                }
            });
        },

    });


    return $.tm.sliderItemLayerText;
});