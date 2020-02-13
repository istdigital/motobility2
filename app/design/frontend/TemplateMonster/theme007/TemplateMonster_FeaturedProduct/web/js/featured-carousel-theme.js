/**
 * Copyright © 2015. All rights reserved.
 */

define([
    'jquery',
    'featuredCarousel'
], function($){
    "use strict";

    $.widget('TemplateMonster.featuredCarouselTheme', $.TemplateMonster.featuredCarousel, {


        options: {
            responsive : {
                0 : {
                    items: 1
                },
                450 : {
                    items: 1
                },
                600 : {
                    items: 2
                },
                767 : {
                    items: 2
                },
                979 : {
                    items: 3
                }
            },
        },

        _create: function() {
            var owl = this.element;
            if($(owl).closest('.featured[role="tablist"]').length){
                this.options.responsive[600] = { items: 1};
            }
            if($(owl).closest('.featured [role="tablist"]').length){
                this.options.responsive[600] = { items: 1};
            }

            owl.owlCarousel(this.options);
            owl.on('resized.owl.carousel', function(){
                $(window).trigger('resize');
            })
        },

    });

    return $.TemplateMonster.featuredCarouselTheme;

});
