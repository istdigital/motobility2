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
],function($){
    'use strict';

    /**
     * Duplicate layout item
     */
    $.widget('tm.sliderItemLayerDuplicate', $.tm.sliderPageEdit, {

        _create: function () {

            this._on({
                click: function (event) {

                    if(0 < this._getLayerItemId()){
                        this._setDataFromForm(this._getLayerItemId(),this._getItemsLayoutAndAnimationSelector());
                    }

                    var canvasSortableItems = this._getCanvasSortableItemsSelector();
                    if($(canvasSortableItems).find('div[class*="selected"]').length > 0){
                        var currentLayerId = this._getLayerItemId();
                        if(currentLayerId) {
                            var layerData = this._getLayoutData(currentLayerId);
                            var layoutNumber = this._getLayerNumber();

                            layerData.sortOrder = layoutNumber;
                            this._setLayoutData(layoutNumber,layerData);
                            if(this._returnObjectValue(layerData,'layer_images'))
                            {
                                $(this.options.sliderLayerImage).val(layerData.layer_images);
                                $(this.options.sliderLayerImage).trigger('change');
                                var triggeredFields = {preview_width_layer:"change",preview_height_layer:"change"};
                                this._fillFormFromObject(this._getItemsLayoutAndAnimationSelector(),
                                    layerData,
                                    triggeredFields
                                );
                                var div = $(canvasSortableItems).find('div[class*="selected"]');

                                if(this._returnObjectValue(layerData,'data-height')){
                                    div.css("height",this._returnObjectValue(layerData,'data-height'));
                                }

                                if(this._returnObjectValue(layerData,'data-width')){
                                    div.css("width",this._returnObjectValue(layerData,'data-width'));
                                }
                            } else if(this._returnObjectValue(layerData,'layer_text_html')) {
                                var ilItem = [];
                                var divItem = [];
                                var itemName = 'Layer-text-' + layoutNumber;

                                layerData.layer_text_html = itemName;
                                ilItem.push(this._createIlSortableItemText(layoutNumber,layerData));
                                divItem.push(this._createTextDiv(layoutNumber,layerData));
                                this._addImageToCanvas(divItem);
                                var sortedIl = ilItem.sort(this._sortOrderLayourFunction);
                                this._addItemToSortable(sortedIl);
                                $(this.options.imageCanvasBlock).find('div[data-id="'+layoutNumber+'"]').trigger('click');
                                this._incrementLayerNumber();
                            }
                        }
                    }
                }
            });

            /**
             * change state of current button by event
             * @type {*|string}
             */
            var canvasSortableItems = this._getCanvasSortableItemsSelector();
            if($(canvasSortableItems).find('div[data-id],li[data-id]').hasClass("selected")) {
                this._makeButtonEnable(this.element);
            }

            /**
             * change state of current button by event
             * @type {*|string}
             */
            $(canvasSortableItems).on(this.options.eventLayerItemClicked,
                $.proxy(function(event,eventElement){
                    this._makeButtonEnable(this.element);
                },this));

            /**
             * change state of current button by event
             * @type {*|string}
             */
            $(canvasSortableItems).on(this.options.eventLayerItemManyRemoved,$.proxy(function(event){
                this._makeButtonDisable(this.element);
            },this));

            /**
             * change state of current button by event
             * @type {*|string}
             */
            $(canvasSortableItems).on(this.options.eventLayerItemRemoved,$.proxy(function(event){
                if($(canvasSortableItems).find('div[data-id],li[data-id]').hasClass("selected")) {
                    this._makeButtonEnable(this.element);
                } else {
                    this._makeButtonDisable(this.element);
                }
            },this));

        },
    });

    return $.tm.sliderItemLayerDuplicate;
});