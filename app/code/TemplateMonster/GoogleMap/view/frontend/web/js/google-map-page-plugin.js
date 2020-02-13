/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */


define([
    'jquery',
    'mage/template',
    'text!TemplateMonster_GoogleMap/templates/plugin.html',
    'jquery/ui',
    'domReady!'
], function ($, mageTemplate, pluginTemplate) {
    'use strict';

    $.widget('TemplateMonster.googleMapPagePlugin', {

        options: {
            pluginPageData: {},
            pagetype: {
                home: 'cms-index-index',
                contacts: 'contact-index-index'
            },
            contactSelector: '.page-title-wrapper',
            template: pluginTemplate,
            group: {}
        },

        _create: function () {
            var pageData = this.options.pluginPageData;

            if (!pageData) {
                return false;
            }

            var $this = this;
            var page = $this._getCurrentPageType();

            if (page || pageData.footer) {
                var pageType = (page) ? page.type : pageData.footer;
                var selector = '';
                $.each(pageData, function (group, data) {
                    if (group == pageType || group == 'footer') {
                        data.group = group;
                        $this._setWidth(data);
                        $this._setHeight(data);
                        selector = $this._getSelector(group);
                        if (!selector) {
                            return false;
                        }
                        $this._insertMap(selector, group, data);
                    }
                });
            }
        },

        _setWidth: function (data) {
            if (data.width !== null) {
                if (data.width.slice(-1) != '%') {
                    data.width += 'px';
                }
            }
        },

        _setHeight: function (data) {
            if (data.height !== null) {
                if (data.height.slice(-1) != '%') {
                    data.height += 'px';
                }
            }
        },

        _insertMap: function (selector, group, data) {
            if (selector == 'before_footer') {
                $('footer').prepend(this._googleMapPluginCode(data));
            } else if (selector == 'after_footer') {
                $('footer').append(this._googleMapPluginCode(data));
            } else {
                $(selector).append(this._googleMapPluginCode(data));
            }
        },

        _getSelector: function (group) {
            switch (group) {
                case 'home':
                    return this.options.pluginPageData.home.show_on;
                case 'contacts':
                    return this.options.contactSelector;
                case 'footer':
                    return 'footer ' + this.options.pluginPageData.footer.selector;
                default:
                    return false;
            }
        },

        _getCurrentPageType: function () {
            var body = $('body');
            var pageType = null;
            $.each(this.options.pagetype, function (index, value) {
                if (body.hasClass(value)) {
                    pageType = {class: value, type: index};
                }
            });
            return pageType;
        },

        _googleMapPluginCode: function (pageData) {
            var source = this.options.template;
            var data = pageData;
            var template = mageTemplate(source);
            return template({
                data: data
            });
        },

    });

    return $.TemplateMonster.googleMapPagePlugin;
});