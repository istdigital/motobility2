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
     * Widget trigger event on insert image to canvas
     *
     */

    $.widget('tm.sliderItemLayerImage', $.tm.sliderPageEdit,{

        options: {
            layerItemDefault: null,
            layerButtonAdd: '#add_layer_button'
        },

        _create: function() {

            this._setImagesFromDbData();
            //this._removeAllLayoutItems();

            $(this.options.layerButtonAdd).on('click',$.proxy(function(event){
                if(0 < this._getLayerItemId()){
                    this._setDataFromForm(this._getLayerItemId(),this._getItemsLayoutAndAnimationSelector());
                }
            },this));

            var canvasSortableItems = this._getCanvasSortableItemsSelector();
            $(canvasSortableItems).on(this.options.eventLayerItemRemoved,$.proxy(function(event){
                $(this.options.imageCanvasBlock).find('div[data-id]').first().trigger('click');
            },this));

            $(canvasSortableItems).on(this.options.eventLayerItemAdded,$.proxy(function(event){
                $(this.options.imageCanvasBlock).find('div[data-id]').last().trigger('click');
            },this));

            //Observ input if insert file name set to background.
            this._on({
                change: function(event)
                {
                    var layerNumber = this._getLayerNumber();
                    this._incrementLayerNumber();
                    this._setLayerItemId(layerNumber);
                    this._resetItemLayoutForm();
                    var itemData = {sortOrder: layerNumber};
                    this._setLayoutData(layerNumber,itemData);
                    var imageSrc = $(event.target).val();
                    var fullImageSrc = this._getFullImagePath(imageSrc);
                    var div = this._createImageDiv(layerNumber,fullImageSrc,itemData);
                    this._addImageToCanvas(div);
                    var li = this._createIlSortableItemImg(layerNumber,itemData);
                    this._addItemToSortable(li);
                    $(this.options.imageCanvasBlock).find('div[data-id="'+layerNumber+'"]').trigger('click');
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
                    if(itemData.hasOwnProperty('layer_images')) {
                       var imageBasePath = this._getFullImagePath(itemData.layer_images);
                       ilItem.push(this._createIlSortableItemImg(layerNumber,itemData));
                       divItem.push(this._createImageDiv(layerNumber,imageBasePath,itemData));
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

        _setActionFistInserItem: function(){
            $(this.options.imageCanvasBlock).find('div[class="ui-draggable"]').last().trigger('click');
        }    
    });

    return $.tm.sliderItemLayerImage;
});