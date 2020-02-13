/**
 * Copyright © 2015. All rights reserved.
 */

define([
    'jquery',
    'brandOwlCarousel'
], function($){
    "use strict";

    $.widget('TemplateMonster.brandCarousel', {


        options: {
            items: 3,
            itemsDesktop: [1199,5],
            itemsDesktopSmall: [979,4],
            itemsTablet: [768,3],
            itemsMobile: [400,2],
            autoPlay: false,
            navigation:true,
            pagination: false,
            addClassActive: true,
            navigationText: [],
        },

        _create: function() {
            this.element.owlCarousel(this.options);
        },
        
    });

    return $.TemplateMonster.brandCarousel;

});
