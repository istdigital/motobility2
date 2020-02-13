/**
 * Copyright © 2017. All rights reserved.
 */

define([
    'jquery',
    'jquery/ui',
    'tabs',
    'underscore',
], function($, _){
    "use strict";

    $.widget('TemplateMonster.widgetTabs', {

        options: {
            tabSections: [],
            widgetInstance: '',
            wysiwygWidgetInstance: ''
        },
        tabsContainer: '',

        _create: function() {

            // Check widget for specified instance
            if(typeof($('#instance_code').val()) != 'undefined' &&
                      $('#instance_code').val() != this.options.widgetInstance) return false;
            if(typeof($('#select_widget_type').val()) != 'undefined' &&
                      $('#select_widget_type').val() != this.options.wysiwygWidgetInstance) return false;

            // _.bindAll(this);
            var widget = this;
            var mainSection = $(widget.element);
            var firstField = $('.admin__field', mainSection).first();
            firstField.before('<div class="widget-tabs"></div>');
            this.tabsContainer = $('.widget-tabs', mainSection);

            this._createTabsSkeleton();
            this._fillTabs();
            this._initTabs();
        },

        _createTabsSkeleton: function(){
            // Generate Tab Titles
            var tabs = '<ul class="tabs-titles">';
            $.map(this.options.tabSections, function (section, index) {
                tabs += '<li><a href="#fragment-'+index.toLowerCase()+'"><span>'+index+'</span></a></li>';
            });
            tabs += '</ul>';
            // Generate Tab Contents
            $.map(this.options.tabSections, function (section, index) {
                tabs += '<div id="fragment-'+index.toLowerCase()+'" class="content" data-role="content" />';
            });
            this.tabsContainer.append(tabs);
        },

        _fillTabs: function () {
            var target, section  = '';
            $.map(this.options.tabSections, function (fieldsArray, fragmentKey) {
                $.map(fieldsArray, function (field) {
                    target = $('.widget-tabs #fragment-'+fragmentKey.toLowerCase());
                    section = $('.admin__field.field[class*='+field+']');
                    $(target).append(section);
                });
            });
        },

        _initTabs: function () {
            this.tabsContainer.tabs({
                "openedState": "active",
                "active": 0
            });
        }
        
    });

    return $.TemplateMonster.widgetTabs;

});
