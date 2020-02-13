define([
    'jquery',
    'jquery/ui',
    'googleMapPagePlugin'
], function($) {
    'use strict';

    $.widget('TemplateMonster.googleMapOptions', $.TemplateMonster.googleMapPagePlugin, {

        options: {
            pluginPageData: {},
            pagetype: {
                home: 'cms-index-index',
                contacts: 'contact-index-index',
            },
            contactSelector: '.google-map-wrapper',
            group: {}
        },

        _create: function() {
            this._super();
        }

    });

    return $.TemplateMonster.googleMapOptions;
});