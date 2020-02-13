define([
    'jquery',
    'underscore'
], function ($, _) {
    'use strict';

    $.widget('TemplateMonster.productListingGallery', {

        options: {},

        _create: function() {
            $(this.element).closest('a').contents().unwrap();
            $(this.element).on('fotorama:load', function (e, fotorama) {
                $('.fotorama__wrap--toggle-arrows', this.element).addClass('fotorama__wrap--no-controls');
            });
        },

    });

    return $.TemplateMonster.productListingGallery;
});