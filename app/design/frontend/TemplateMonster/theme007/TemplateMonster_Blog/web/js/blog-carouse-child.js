/**
 * Copyright © 2015. All rights reserved.
 */

define([
    'jquery',
    'blogOwlCarousel',
    'blogCarousel'
], function($){
    "use strict";

    $.widget('TemplateMonster.blogCarouselChild', $.TemplateMonster.blogCarousel, {

        options: {
            items: 3,
            itemsDesktop:false,
            itemsDesktopSmall: [979, 2],
            itemsTablet: [768, 1],
            itemsMobile: [400, 1]
        },

        _create: function() {
            if($(".widget.blog-posts .list.post-items").length){
                console.log('sasd');
                this.options.itemsDesktopSmall = [979, 1]
            }
            this._super();
        }
        
    });

    return $.TemplateMonster.blogCarouselChild;

});
