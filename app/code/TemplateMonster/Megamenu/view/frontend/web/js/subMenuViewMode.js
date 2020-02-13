define([
    "jquery",
    "matchMedia",
], function ($, mediaCheck) {
    "use strict";

    $.widget('TemplateMonster.subMenuViewMode', {

        _create: function() {
            this._toggleSubCategories(this.element);
            this._subCategoriesLink();

        },

        _toggleSubCategories: function ( mmSubmenu ) {
            mmSubmenu.each(function () {
                var submenu = $(this)
                if ( submenu.hasClass('vm-pop-up') || submenu.hasClass('vm-easing')) {
                    mediaCheck({
                        media: '(max-width: 767px)',
                        entry: function () {
                            submenu.show();
                            submenu.parent('li').unbind('hover');
                        },
                        exit: function () {
                            submenu.hide();
                            submenu.parent('li').hover(function () {
                                setTimeout(function () {
                                    submenu.fadeIn(0);
                                }, 200);

                            }, function () {
                                setTimeout(function () {
                                    submenu.fadeOut(0);
                                }, 300);
                            });
                        }
                    });
                } else { return; }
            });
        },
        _subCategoriesLink: function() {
            var mm_submenu = $('.mm-submenu');
            if($(mm_submenu).length){
                mediaCheck({
                    media: '(max-width: 767px)',
                    entry: function () {
                        $('.mm-submenu li > a').on('click touchend', function(e) {
                            var el = $(this);
                            var link = el.attr('href');
                            window.location = link;
                        });
                    },
                    exit: function () {
                    }
                });
            }

        }
    });

    return $.TemplateMonster.subMenuViewMode;
});