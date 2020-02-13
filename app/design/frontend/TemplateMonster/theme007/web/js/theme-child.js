define([
    'jquery',
    'jquery/ui',
    'theme'
], function ($) {
    'use strict';

    $.widget('TemplateMonster.themeChild', $.TemplateMonster.theme, {

        options: {
            flagres: true, // max 991
            flagres_2: true, // max 767
        },
        widthWinId: function () {

            var width_win = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
            if (width_win > 767) {
                if (!this.options.flagres_2) {
                    this.options.flagres_2 = true;
                }
            } else {
                if (this.options.flagres_2) {
                    this.options.flagres_2 = false;
                }
            }
        },
        hide_footer_col: function () {
            var idClick = '.footer-col h4';
            var idSlide = '.footer-col .footer-col-content';
            var idClass = 'id-active';

            $(idClick).on('click', function (e) {
                e.stopPropagation();

                if($(window).width() <= 767){
                    var subUl = $(this).next(idSlide);

                    if (subUl.is(':hidden')) {
                        subUl.slideDown();
                        $(this).addClass(idClass);
                    }
                    else {
                        subUl.slideUp();
                        $(this).removeClass(idClass);
                    }

                    $(idClick).not(this).next(idSlide).slideUp();

                    $(idClick).not(this).removeClass(idClass);
                }

                return false;
            });
            $(idSlide).on('click', function (e) {
                e.stopPropagation();
            });
        },
        hide_settings_: function () {
            var elClick = '.elClick';
            var elSlide = 'elSlide';
            var elClass = 'id-active';

            $(elClick).on('click', function (e) {
                e.stopPropagation();
                if ($(this).parent().hasClass(elClass)) {
                    $(this).parent().removeClass(elClass);
                } else {
                    $(this).parent().addClass(elClass);
                }
                $(elClick).not(this).next(elSlide).removeClass(elClass);
                $(elClick).not(this).parent().removeClass(elClass);
                e.preventDefault();
            });

            $(elSlide).on('click', function (e) {
                e.stopPropagation();
            });
        },
        carusel_related_post: function () {
            if($(".related-post-list").length) {
                $(".related-post-list").owlCarousel({
                    items: 3,
                    autoPlay: 1400000,
                    itemsDesktop: [1199, 3],
                    itemsDesktopSmall: [979, 2],
                    stopOnHover: true,
                    pagination: false,
                    transitionStyle: false,
                    navigation: true
                });
            }

        },
        hide_filter_: function () {
            var elClick = '.filter-toggle-full';
            var elSlide = '.grid-left-fixed';
            var elClass = 'id-active';

            $(elSlide).append('<span class="close">Close</span>');
            $('.column').on('click', elClick, function (e) {
                e.stopPropagation();
                if ($(elSlide).hasClass(elClass)) {
                    $(elSlide).removeClass(elClass);
                } else {
                    $(elSlide).addClass(elClass);
                }
                e.preventDefault();
            });
            $('body, .grid-left-fixed .close').on('click', function () {
                $(elSlide).removeClass(elClass);
            });
            $(elSlide).on('click', function (e) {
                e.stopPropagation();
            });
        },
        cursor_search: function () {
            $('.rd-navbar-search-toggle').on("click", function () {
                setTimeout(function () {
                    $('.rd-navbar-search #search').focus();
                }, 200);
            });
        },
        border_class_fix:function () {
            if($(".parallax-container").length){
                $('.parallax-container').prev().addClass('border-none');
            }
            if($(".custom-item-7").length){
                $('.custom-item-7').parent().addClass('border-fix-none');
            }
            if($(".custom-item-8").length){
                $('.custom-item-8').parent().addClass('border-none-content');
            }
            if($(".custom-item-12, .custom-item-14, .custom-item-15, .custom-item-26, .custom-item-27").length){
                $('.custom-item-12, .custom-item-14, .custom-item-15, .custom-item-26, .custom-item-27').parent().addClass('border-none-content');
            }
            if($(".custom-item-28").length){
                $('.custom-item-28').parent().addClass('border-fix-indent fix-indent-top');
            }
            if($("#customization_tool").length){
                $('#switcher-language').css({'display':'none'});
            }
            if($(".main .widget").length){
                //$('.main .widget').parents('.page-main').addClass('');
            } else {
                $('.main').parents('.page-main').addClass('fix-indent');
            }

        },
        last_widget: function () {
            $( ".column .widget" ).last().addClass('last_widget');
        },
        insert_logo: function () {
            $( ".footer .logo" ).insertAfter('.footer .logoAfter');
        },
        title_wrap: function () {
            var class_title = $('.category-grid-full .category-name');
            $(class_title).each(function () {
                var str = $(this).html();
                var pos = str.lastIndexOf(' ');
                if(pos > 0){
                    str = str.substr(0, pos) + ' <span>' + str.substr(pos + 0) + '</span>';
                    $(this).html(str);
                }
            });
        },

        _create: function () {
            this._super();
            this.widthWinId();
            this.hide_footer_col();
            this.cursor_search();
            this.hide_settings_();
            this.carusel_related_post();
            this.last_widget();
            this.insert_logo();
            this.hide_filter_();
            this.border_class_fix();
            this.title_wrap();
            var q = this;
            $(window).bind('load.child resize', function () {
                clearTimeout(this.id);
                this.id = setTimeout(q.widthWinId(), 300);
            }).trigger('load.child');
        }
    });

    return $.TemplateMonster.themeChild;

});