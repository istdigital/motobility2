/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

/**
 * Base widget for slide edit page. It keeps state , data , increment id for items layout;
 * Has properties with shared functionality.
 */
define([
    'jquery'
],function($){
'use strict';
    $.widget('tm.sliderPageEdit', {

        options: {
            sliderItemFieldsSet: "#slider_base_fieldset_layer",
            sliderItemAnimationFieldsSet: "#slider_base_fieldset_layer_animation",
            sliderItemTextFieldsSet: "#slider_base_fieldset_layer_text",
            imageCanvasBlock: "#imagecanvas",
            sortableLayerItems: "#sortable",
            stylingLayerElementCss: {"border-color": "","border-weight":"","border-style":""},
            resetStylingLayerElementCss: {"border-color": "","border-weight":"","border-style":""},
            removeLayerButton: "#remove_layer_button",
            removeAllLayerButton: "#remove_layer_all_button",
            eventLayerItemClicked: "layerItemClicked",
            eventLayerItemAdded: "layerItemAdded",
            eventLayerItemRemoved: "layerItemRemoved",
            eventLayerItemManyAdded: "layerItemManyAdded",
            eventLayerItemManyRemoved: "layerItemManyRemoved",
            sliderLayerImage: "#slider_layer_images",
            sliderLayerImageUpdate: "#update_layer_button",
            basePathWysiwygImage:"",
            zindex: 100,
            sliderLayerImageFieldSet : '#slider_base_fieldset_image',
            sliderLayerImageParams : '#slider_image_params',
            sliderLayerGeneralParams: '#slider_layer_general_params',
            sliderLayerTextBackColor: '#slider_layer_text_back_color',
            sliderLayerTextColor: '#slider_layer_text_color',
            sliderBaseFieldsetLayerText: 'legend[aria-controls*="slider_base_fieldset_layer_text"]',

        },

        /**
         * Global value contain data for every
         * layer item. Key - value.
         * Use getter and setter for access
         */
        layerItem : {},

        /**
         * Global value contain number(int)
         * of last insert layer
         * Use getter ot increment functions
         * for access.
         */
        layerNumber : {number:1},


        /**
         * Get last insert id of layout
         *
         * @returns {number}
         * @private
         */
        _getLayerNumber: function(){
            return this.layerNumber.number;
        },

        /**
         * Increment id of layout
         *
         * @private
         */

        _incrementLayerNumber: function(){
            this.layerNumber.number = this.layerNumber.number + 1;
        },

        /**
         * set data for layer storage by id
         *
         * @param number
         * @param layoutData
         * @private
         */
        _setLayoutData: function(number,layoutData){
            this.layerItem[number] = layoutData;
        },

        /**
         * Return data of layout by id
         *
         * @param number
         * @returns {{tm.sliderPageEdit.layerItem[id]} | undefined}
         * @private
         */
        _getLayoutData: function(number){
            return this.layerItem[number];
        },

        /**
         * return all avalaible layout
         *
         * @returns {tm.sliderPageEdit.layerItem|{}}
         * @private
         */
        _getLayoutAll: function(){
            return this.layerItem;
        },


        /**
         * Reset tm.sliderPageEdit.layerItem[id]| item's form
         *
         * @param number
         * @private
         */
        _removeLayoutDataById: function(number) {
            delete this.layerItem[number];
            this._resetLayerItemId();
            this._resetItemLayoutForm();
            this._resetItemLayoutImgForm();
        },

        /**
         * remove all layouts data
         * @private
         */
        _clearLayoutData: function() {
            this.layerItem = {};
        },

        /**
         * set layer id data attr storage
         *
         * @param layerItemId
         * @private
         */
        _setLayerItemId: function(layerItemId){
            $(this.options.sliderItemFieldsSet).data("layernumber",layerItemId);
        },

        /**
         *  set layer id data attr storage
         *
         * @returns {int|*|jQuery}
         * @private
         */
        _getLayerItemId: function(){
            return $(this.options.sliderItemFieldsSet).data("layernumber");
        },

        /**
         *  reset layer id data attr storage
         *
         * @returns {int|*|jQuery}
         * @private
         */
        _resetLayerItemId: function(){
            return $(this.options.sliderItemFieldsSet).data("layernumber","");
        },

        /**
         * Reset form of layout exclude image
         *
         * @private
         */
        _resetItemLayoutForm: function(type){
            if(type=='text') {
                $(this._getItemsLayoutAndAnimationSelector()).find('input,select,textarea').val('');
            } else {
                $(this._getItemsLayoutAndAnimationSelector()).find('input[id!=slider_layer_images],select,textarea').val('');
            }
        },

        /**
         * reset layout image
         *
         * @private
         */
        _resetItemLayoutImgForm: function(){
            $(this.options.sliderItemFieldsSet).find('input[id=slider_layer_images]').val('');
        },

        /**
         * set data into layout data storage
         * from layout item form
         *
         * @param itemNumber
         * @private
         */
        _setDataFromForm: function(itemNumber,elementId){
            var formDataMapObj = this._getFormDataArray(elementId);
            if(this._getLayoutData(itemNumber)) {
                var layoutData =  this._getLayoutData(itemNumber);
                if(layoutData && layoutData.hasOwnProperty('sortOrder') && layoutData.sortOrder)
                {
                    var sortOrder = layoutData.sortOrder;
                    formDataMapObj.sortOrder = layoutData.sortOrder;
                }
            }

            this._setLayoutData(itemNumber,formDataMapObj);
        },

        /**
         * function execute when layout
         * item(div or li) selected
         *
         * @param event
         * @returns {boolean}
         * @private
         */
        _selectLayerItem: function(event)
        {
            if($(event.target).hasClass('selected')) {return false;}

            //Save data to from
            if(0 < this._getLayerItemId()){
                this._setDataFromForm(this._getLayerItemId(),this._getItemsLayoutAndAnimationSelector());
            }

            var type = $(event.target).data('type');

            this._resetItemLayoutForm(type);
            this._unsetSelectItemStyle();

            var itemId = $(event.target).data('id');
            this._setLayerItemId(itemId);
            if(this._getLayoutData(itemId))
            {
                var itemData = this._getLayoutData(itemId);
                var selectorForm = this._getItemsLayoutAndAnimationSelector();

                this._fillFormFromObject(selectorForm,itemData);

            }
            this._setSelectItemStyle(itemId);
        },

        /**
         * Create object from form of layout items
         *
         * @returns {{}}
         * @private
         */
        _getFormDataArray : function(elementId){
            var objSliderImage = {};
            var fieldSet;

            fieldSet = $(elementId).serializeArray();
            $.each( fieldSet, function( i, l ){
                var name = l.name;
                var value = l.value;
                objSliderImage[name] = value;
            });
            return objSliderImage;
        },

        _getItemsLayoutAndAnimationSelector: function() {
          return this.options.sliderItemFieldsSet + ' , ' + this.options.sliderItemAnimationFieldsSet +
              ' , ' + this.options.sliderItemTextFieldsSet;
        },

        /**
         *
         * Resturn string of selector
         *
         * @returns {string}
         * @private
         */
        _getCanvasSortableItemsSelector: function(){
            return this.options.sortableLayerItems +','+this.options.imageCanvasBlock;
        },

        /**
         * Reset all selected layout item in canvas and sortable ul
         *
         * @private
         */
        _unsetSelectItemStyle: function(){
            var canvasSortableItems = this._getCanvasSortableItemsSelector();
            $(canvasSortableItems).find('div[data-id],li[data-id]')
                .css(this.options.resetStylingLayerElementCss)
                .removeClass('selected');
        },

        /**
         * Set class selected item in canvas and sortable ul
         * to item by id
         *
         * @param dataId
         * @private
         */
        _setSelectItemStyle: function(dataId){
            var canvasSortableItems = this._getCanvasSortableItemsSelector();
            $(canvasSortableItems).find('div[data-id="'+dataId+'"],li[data-id="'+dataId+'"]').css(
                this.options.stylingLayerElementCss
            ).addClass('selected');
        },

        /**
         * Change z-index in canvas for div item
         *
         * @param sortableItems
         * @private
         */
        _changeLayoutImageZindex: function(sortableItems){
            $.each(sortableItems, $.proxy(function(item){
                var itemId = sortableItems[item];
                var zindex = this.options.zindex + item;
                $(this.options.imageCanvasBlock).find('div[data-id="'+itemId+'"]').css("z-index",parseInt(zindex,10));
            },this));
        },

        /**
         * Add sort order for layout
         * items in data storage
         *
         * @param sortableItems
         * @private
         */
        _changeSortableItemOrder: function(sortableItems) {
            $.each(sortableItems, $.proxy(function(item){
                var layerId = sortableItems[item];
                var sortOrder = ++item;
                var layerDataObject = this._getLayoutData(layerId);
                if(layerDataObject) {
                    layerDataObject.sortOrder = sortOrder;
                    this._setLayoutData(layerId,layerDataObject);
                }

            },this));
        },

        /**
         * Remove item from canvas  and sortable ul by id
         *
         * @param dataId
         * @private
         */
        _removeImageSortableItemById: function(dataId){
            var canvasSortableItems = this._getCanvasSortableItemsSelector();
            $(canvasSortableItems).find('div[data-id="'+dataId+'"],li[data-id="'+dataId+'"]').remove();
        },

        /**
         *
         * Create pull path image
         *
         * @param imgSrc
         * @returns {*}
         * @private
         */
        _getFullImagePath: function(imgSrc){
            return this.options.basePathWysiwygImage + imgSrc;
        },

        /**
         *
         * @param targetElement
         * @private
         */
        _makeButtonEnable: function(targetElement){
            var buttonSelector = $(targetElement);
            if($(buttonSelector).is(":disabled")) {
                $(buttonSelector).attr("disabled",false).removeClass("disabled");
            }
        },

        /**
         *
         * @param targetElement
         * @private
         */
        _makeButtonDisable: function(targetElement){
            var buttonSelector = $(targetElement);
            if(!$(buttonSelector).is(":disabled")) {
                $(buttonSelector).attr("disabled","disabled").addClass("disabled");
            }
        },

        _fillFormFromObject: function(selectorForm,itemData,triggerFields){
            for(var item in itemData) {
                if(itemData[item]) {
                    var field = $(selectorForm).find('[name="'+item+'"]');
                    field.val(itemData[item]);
                    if(triggerFields  && triggerFields[item])
                    {
                        field.trigger(triggerFields[item]);
                    }
                }
            }
        },

        _sortOrderLayourFunction: function(a,b){
            if (a.data('sortorder') < b.data('sortorder')) {
                return -1;
            } else if (a.data('sortorder') > b.data('sortorder')) {
                return 1;
            } else {
                return 0;
            }
        },


        _createJSONDataBeforeSave: function(){
            if(0 < this._getLayerItemId()){
                this._setDataFromForm(this._getLayerItemId(),this._getItemsLayoutAndAnimationSelector());
            }

            var jsonString = JSON.stringify(this._getLayoutAll());
            var jsonStringImg = JSON.stringify(this._getFormDataArray(this.options.sliderLayerImageFieldSet));
            $(this.options.sliderLayerGeneralParams).val(jsonString);
            $(this.options.sliderLayerImageParams).val(jsonStringImg);
        },

        _returnObjectValue: function(objData,index){
            if(objData && objData.hasOwnProperty(index) && objData[index]){
                return objData[index];
            }
            return false;
        },

        _returnIntOrFalse: function(number){
            var numberResult = parseInt(number,10);
            return Number.isNaN(numberResult) ? false : numberResult;
        },

        _getLayerItemSelected: function(type){
            var layerId = this._getLayerItemId();
            var div = $(this.options.imageCanvasBlock).find('div[data-id="'+layerId+'"]');

            if(div && (div.data('type') == type)) {
                return div;
            }
            return false;
        },

        _getLayerItemSelectedLi: function(type){
            var layerId = this._getLayerItemId();
            var li = $(this.options.sortableLayerItems).find('li[data-id="'+layerId+'"]');

            if(li && (li.data('type') == type)) {
                return li;
            }
            return false;
        },

        _addImageToCanvas: function(div) {
            $(this.options.imageCanvasBlock).append(div);
        },

        _addItemToSortable: function(li){
            $(this.options.sortableLayerItems).append(li);
        },

        _textSettingFieldDisable:function() {
            $(this.options.sliderLayerTextBackColor).trigger('change');
            $(this.options.sliderLayerTextColor).trigger('change');
            $(this.options.sliderItemTextFieldsSet).find('textarea,input,select').prop( "disabled", true );
            $(this.options.sliderBaseFieldsetLayerText).attr('aria-selected',false).attr('aria-expanded',false).hide();
            $(this.options.sliderItemTextFieldsSet).hide();
        },

        _textSettingFieldUnable:function() {
            $(this.options.sliderLayerTextBackColor).trigger('change');
            $(this.options.sliderLayerTextColor).trigger('change');
            $(this.options.sliderItemTextFieldsSet).find('textarea,input,select').prop( "disabled", false );
            $(this.options.sliderBaseFieldsetLayerText).show();
        },

        _createIlSortableItemImg: function(itemNumber,itemData){
            var itemId = 'layer' + '-' + itemNumber;
            var sortOrder = 0;
            if(itemData && itemData.hasOwnProperty('sortOrder') && itemData.sortOrder)
            {
                sortOrder = itemData.sortOrder;
            }

            var li = $('<li/>',{
                'id':itemId,
                'data-id' : itemNumber,
                'data-sortorder' : sortOrder,
                'data-type' : 'image',
            });

            var itemName = '';
            if(itemData && itemData['preview_name_layer']) {
                itemName = itemData['preview_name_layer'];
            } else {
                itemName = 'Layer-image-' + itemNumber;
            }

            li.html(itemName);
            return li;
        },

        _createImageDiv: function(itemNumber,fullImageSrc,itemData){
            var div = $('<div/>',{
                'id':'layer' + '-' + itemNumber,
                'data-id' : itemNumber,
                'data-type' : 'image',
            });

            return div;
        },

        _createIlSortableItemText: function(itemNumber,itemData){
            var itemId = 'layer' + '-' + itemNumber;
            var sortOrder = 0;
            if(itemData && itemData.sortOrder)
            {
                sortOrder = itemData.sortOrder;
            }

            var li = $('<li/>',{
                'id':itemId,
                'data-id' : itemNumber,
                'data-sortorder' : sortOrder,
                'data-type' : 'text',
            });

            var itemName = '';

            if(itemData && itemData['preview_name_layer']) {
                itemName = itemData['preview_name_layer'];
            } else {
                itemName = 'Layer-text-' + itemNumber;
            }

            li.html(itemName);
            return li;
        },

        _createTextDiv: function(itemNumber,textData){

            var div = $('<div/>',{
                'id':'layer' + '-' + itemNumber,
                'data-id' : itemNumber,
                'data-type' : 'text',
            });

            if((typeof textData === 'object') && this._returnObjectValue(textData,'sortOrder')){
                div.css("z-index",parseInt(this.options.zindex,10) + parseInt(this._returnObjectValue(textData,'sortOrder'),10));
            } else {
                div.css("z-index",parseInt(this.options.zindex,10) + parseInt(this._getLayerNumber(),10));
            }

            return div;
        },

    });

    return $.tm.sliderPageEdit;

});