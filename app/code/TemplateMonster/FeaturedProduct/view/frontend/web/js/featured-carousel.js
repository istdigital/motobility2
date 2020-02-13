/**
 * Copyright © 2015. All rights reserved.
 */

define([
    'jquery',
    'featuredOwlCarousel'
], function($){
    "use strict";

    $.widget('TemplateMonster.featuredCarousel', {
        options: {
            margin: 0,
            nav:true,
            navText: [],
            dots: false,
            autoplay: false,
            responsive : {
                0 : {
                    items: 2
                },
                450 : {
                    items: 3
                },
                600 : {
                    items: 3
                },
                767 : {
                    items: 4
                },
                979 : {
                    items: 4
                },
                1199 : {
                    items: 4
                }
            },
            inSidebar: false,
            touchDrag: false,
            mouseDrag: false,
            sidebarOptions: {
                0 : { items: 1},
                450 : { items: 1},
                600 : { items: 1},
                767 : { items: 1},
                979 : { items: 1},
                1199 : { items: 1}
            },
            prevCssClass: '',
            nextCssClass: '',
            defaultPrevCssClass: 'fa-angle-left',
            defaultNextCssClass: 'fa-angle-right'
        },

        _create: function() {
            var self = this;
            this.options.navigation = Boolean(this.options.navigation);
            this.options.pagination = Boolean(this.options.pagination);

            var onInitialized = {
                onInitialized: function () {
                    $('.owl-prev', self.element).addClass(this.options.defaultPrevCssClass+' '+this.options.prevCssClass);
                    $('.owl-next', self.element).addClass(this.options.defaultNextCssClass+' '+this.options.nextCssClass);
                    $(self.element).parent('.is-carousel').animate({ opacity: 1}, 500);
                }
            };
            if(this.options.inSidebar) {
                this.options.responsive = $.extend(this.options.responsive, this.options.sidebarOptions)
            }
            $(this.element).parent('.is-carousel').css({opacity: 0});
            this.element.owlCarousel($.extend(this.options, onInitialized));
        },
        
    });

    return $.TemplateMonster.featuredCarousel;

});
