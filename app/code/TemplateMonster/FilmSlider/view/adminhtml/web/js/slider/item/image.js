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

    /**
     * Observe field. On change event changes slide item image
     * and set background to slide edit page
     */
    $.widget('tm.sliderItemImage', $.tm.sliderPageEdit,{

        options: {
           imageCanvasBlock: "#imagecanvas",
           imagePreviewWidth: "#slider_preview_width",
           imagePreviewHeight: "#slider_preview_height",
        },

        _create: function() {
            var imageSrc;
            //set value on load page
            imageSrc = this.element.val();
            this._setBackgroundCanvas(imageSrc);

            //Observe input if insert file name set to background.
            this._on({
                change: function(event)
                {
                    imageSrc = $(event.target).val();
                    this._setBackgroundCanvas(imageSrc);
                }
            });

            var slideWidthValue = $(this.options.imagePreviewWidth).val();
            var slideHeightValue = $(this.options.imagePreviewHeight).val();

            var width = this._returnIntFromPersente(slideWidthValue);
            var height = this._returnIntFromPersente(slideHeightValue);

            if(width && height)
            {
                $(this.options.imageCanvasBlock).width(width);
                $(this.options.imageCanvasBlock).height(height);
            }
            /**
             * Change backgorund width
             */
            $(this.options.imagePreviewWidth).on('change', $.proxy(function(event){
               var element = $(event.target);
               var width = this._returnIntFromPersente(element.val());
               if(width) {
                   $(this.options.imageCanvasBlock).width(width);
               }
            },this));

            /**
             * Change backgorund height
             */
            $(this.options.imagePreviewHeight).on('change', $.proxy(function(event){
                var element = $(event.target);
                var height = this._returnIntFromPersente(element.val());
                if(height) {
                    $(this.options.imageCanvasBlock).height(height);
                }
            },this));

            $("#save").on("beforeSaveEvent",$.proxy(function(){
                this._createJSONDataBeforeSave();
            },this));

            $("#saveandcontinue").on("beforeSaveEvent", $.proxy(function(){
                this._createJSONDataBeforeSave();
            },this));

        },

        /**
         * Set images to canvas
         *
         * @param imageSrc
         * @private
         */
        _setBackgroundCanvas: function(imageSrc)
        {
            if(!imageSrc){return false;}
            var fullImageSrc = this._getFullImagePath(imageSrc);
            var imageCanvasBlock = $(this.options.imageCanvasBlock);
            imageCanvasBlock.css({
                "background-image" : 'url(' + fullImageSrc + ')',
                "background-repeat": "no-repeat",
                "background-position": "center center",
            });

        },

        _returnIntFromPersente: function(value){
            var valueResult = value.replace("%","");
            if(!valueResult || !this._returnIntOrFalse(valueResult)){return false;}
            return this._returnIntOrFalse(valueResult);
        }
    });

    return $.tm.sliderItemImage;
});