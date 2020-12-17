/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

define([
    'jquery',
    'mage/translate',
    'ko',
    'mage/loader'
], function ($, $t, ko) {

    var isLoading = false;

    $.widget('expert.Scroll', {

        options: {
            icon:null
        },

        _create: function () {
            var toolbar = $('.toolbar-bottom .toolbar-products'),
                toolbarWrap = $('.toolbar-bottom'),
                self = this;

            if(toolbar.length){
                toolbar.hide();

                toolbarWrap.append("<p class='loader' style='display:none;text-align:center'></p>");

                if(this.options.icon){
                    toolbarWrap.find('p').prepend('<img src="'+this.options.icon+'" />');
                }

                //toolbarWrap.find('.loader').loader();
            }


            $(window).scroll(function(){
                var wt = $(window).scrollTop(),
                    et = toolbarWrap.offset().top;

                if(et - wt <= 1000){
                    self.loadNextPage()    
                }                
            })
        },

        loadNextPage(){
            var toolbar = $('.toolbar-bottom .toolbar-products'),
                self = this,
                current = toolbar.find(".pages-items li.current"),
                toolbarWrap = $('.toolbar-bottom'),
                next = toolbar.find(".pages-items li.current").next();

            if(!isLoading && next.length && !next.hasClass('pages-item-next')){
                isLoading = true;
                toolbarWrap.find('.loader').show();

                //toolbarWrap.find('.loader').loader("show");

                $.get(next.find('a').attr('href')).done(function(data){

                    var p = $(data).find(".column.main .product-items").html();

                    $(".column.main .product-items").append(p);

                    //$('.toolbar-products .toolbar-amount').first().html($(data).find('.toolbar-products .toolbar-amount').first().html());
                    current.removeClass('current');
                    next.addClass('current');

                    $(".column.main .product-items").trigger('contentUpdated');

                }).fail(function() {
                    
                }).always(function() {
                    isLoading = false;
                    toolbarWrap.find('.loader').hide();
                    //toolbarWrap.find('.loader').loader("hide");
                });
            }
        }
    });

    return $.expert.Scroll;
});

