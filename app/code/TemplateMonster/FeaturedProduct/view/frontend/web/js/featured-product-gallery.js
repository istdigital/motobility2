define([
    'jquery'
], function ($) {
    'use strict';

    $.widget('TemplateMonster.featuredProductGallery', {

        options: {},

        _create: function() {
            $(this.element).closest('a').contents().unwrap();
            $(this.element).on('fotorama:load', function (e, fotorama) {
                $('.fotorama__wrap--toggle-arrows', this.element).addClass('fotorama__wrap--no-controls');
            });
        },

    });

    return $.TemplateMonster.featuredProductGallery;
});