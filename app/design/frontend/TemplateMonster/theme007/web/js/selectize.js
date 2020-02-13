define([
    'jquery',
    'jquery/ui',
    'customSelect'
], function ($, select2) {
    'use strict';

    $.widget('TemplateMonster.selectize', {

        options: {
            allowClear: false,
        },

        _create: function() {

            var selector = this.element;
            var params = this.options;
            var isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|Windows Phone/i.test(navigator.userAgent)

            if (!isMobile){
                $(selector).select2(params);

                $('.swatch-option').on('click', function(){
                    selector.select2("destroy");
                    selector.select2(params);
                });
                $('body').addClass('no-mobile');
            }
        },
    });

    return $.TemplateMonster.selectize;

});