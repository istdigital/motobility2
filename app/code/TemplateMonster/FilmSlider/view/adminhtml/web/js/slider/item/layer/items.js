define([
    'jquery',
    'TemplateMonster_FilmSlider/js/slider/item/sliderpagedit',
    'TemplateMonster_FilmSlider/js/slider/item/canvas',
    'TemplateMonster_FilmSlider/js/slider/init-accordion',
],function($) {
    'use strict';

    $.widget('tm.sliderItemLayerItems', $.tm.sliderPageEdit, {

        options: {
            layerItemDefault: null,
            layerButtonAdd: '#add_layer_button',
            previewLayerName: "#slider_preview_name_layer"
        },

        _create: function(){
            this._setImagesFromDbData();
            this._changeLayerName();
        },

        _changeLayerName: function() {
            var selector = this.options.previewLayerName;

            $(selector).on('keyup',function(event){
                var value = $(event.target).val();
                if(value) {
                    $('#sortable li[class*="selected"]').html(value);
                }
            });

            $(this.options.imageCanvasBlock).on('click','div[data-id]',function(){
                var value = $(selector).val();
                if(value) {
                    $('#sortable li[class*="selected"]').html(value);
                }
            });

        },

        _setImagesFromDbData: function() {
            if(this.options.layerItemDefault) {

                var ilItem = [];
                var divItem = [];
                var dbLayerItems = this.options.layerItemDefault;

                $.each(dbLayerItems, $.proxy(function(item){
                    var layerNumber = this._getLayerNumber();
                    var itemData = dbLayerItems[item];
                    if(itemData.hasOwnProperty('layer_images') && itemData.layer_images) {
                        var imageBasePath = this._getFullImagePath(itemData.layer_images);
                        ilItem.push(this._createIlSortableItemImg(layerNumber,itemData));
                        divItem.push(this._createImageDiv(layerNumber,imageBasePath,itemData));
                    } else if(itemData.hasOwnProperty('layer_text_html') && itemData.layer_text_html) {
                        ilItem.push(this._createIlSortableItemText(layerNumber,itemData));
                        divItem.push(this._createTextDiv(layerNumber,itemData));
                    }
                    this._setLayoutData(layerNumber,itemData);
                    this._incrementLayerNumber();
                },this));

                this._addImageToCanvas(divItem);

                var sortedIl = ilItem.sort(this._sortOrderLayourFunction);
                this._addItemToSortable(sortedIl);
                $(this.options.imageCanvasBlock).find('div[data-id]').first().trigger('click');
            }
        },

    });


    return $.tm.sliderItemLayerItems;
});