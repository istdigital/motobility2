/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */


define([
    'jquery',
    'MutationObserver'
],function($){


    $.widget('tm.imageCanvas',{

        options: {
           sildeImageFiled : '#slider_slideritem_image',
           sildeImageCanvas : null,
           basePathWysiwygImage: null,
           imageCanvasBlock: "#imagecanvas",
           imagePreviewWidth: "#slider_preview_width",
           imagePreviewHeight: "#slider_preview_height",

        },

        _create: function() {

            //Observ input if insert file name set to background.
            this._on({
                change: function(event)
                {
                    var imageSrc = $(event.target).val();
                    var fullImageSrc = this.options.basePathWysiwygImage + imageSrc;
                    var imagecanvasBlock = $(this.options.imageCanvasBlock);
                    imagecanvasBlock.css({
                        "background-image" : 'url(' + fullImageSrc + ')',
                        "background-repeat": "no-repeat",
                        "background-position": "center center",
                    });
                }
            });

            $(this.options.imagePreviewWidth).on('change', $.proxy(function(event){
               var element = $(event.target);
               var width =  element.val();
               if(width) {
                   $(this.options.imageCanvasBlock).width(width);
               }
            },this));

            $(this.options.imagePreviewHeight).on('change', $.proxy(function(event){
                var element = $(event.target);
                var height =  element.val();
                if(height) {
                    $(this.options.imageCanvasBlock).height(height);
                }
            },this));

            var MutationObserver = window.MutationObserver;

            var that = this;
            var target = document.querySelector(this.options.imageCanvasBlock);
            var observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    for (var i = 0; i < mutation.addedNodes.length; i++) {
                        that._imageFiledHandler(mutation.addedNodes[i]);
                    }
                });
            });
            var config = { attributes: true, childList: true, characterData: true };
            observer.observe(target, config);


            var layerNumber = 1;
            $("#slider_layer_images").on('change',$.proxy(function(event){

                var imageSrc = $(event.target).val();
                var fullImageSrc = this.options.basePathWysiwygImage + imageSrc;

                var div = $('<div/>',{
                    'id':'layer' + '-' + layerNumber
                });

                div.css({
                    "background-image" : 'url(' + fullImageSrc + ')',
                    "background-repeat": "no-repeat",
                    "background-position": "center center",
                    "width" : "50px",
                    "height" : "50px"
                });
                $(this.options.imageCanvasBlock).append(div);
                ++layerNumber;
            },this));


            $( "#sortable" ).sortable();

        },

        _imageFiledHandler : function(node)
        {
            $(node).draggable();
        }

    });

    return $.tm.imageCanvas;
});