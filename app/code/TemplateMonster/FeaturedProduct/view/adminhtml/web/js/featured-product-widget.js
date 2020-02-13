/**
 * Copyright © 2017. All rights reserved.
 */

define([
    'jquery',
    'jquery/ui',
    'underscore',
    'mage/template',
    'text!TemplateMonster_FeaturedProduct/templates/input-title.html'
], function($, ui, _, mageTemplate, titleTemplate){
    "use strict";

    $.widget('TemplateMonster.featuredProductWidget', {

        options: {
            template: titleTemplate
        },
        multiselectBlock: {},
        multiselect: {},
        fieldset: {},

        _create: function() {

            // Check widget for Featured Products instance
            if(typeof($('#instance_code').val()) != 'undefined' &&
                $('#instance_code').val() != 'featured_products') return false;
            if(typeof($('#select_widget_type').val()) != 'undefined' &&
                $('#select_widget_type').val() != "TemplateMonster\\FeaturedProduct\\Block\\FeaturedProduct\\Widget\\Product") return false;

            _.bindAll(this, '_setData', '_onChangeTitle', '_generateFields', '_getMap');
            var widget = this;
            this.multiselectBlock = $("[class*='_product_types']", widget.element);
            this.multiselect = $('select.multiselect', this.multiselectBlock);
            this.fieldset = $(this.multiselectBlock).parents('.admin__fieldset');

            this._setDescription(
                '.admin__field[class*="_categories"] .rule-tree-wrapper',
                'Select category. If choose 2 and more Product Type, categories will be disabled.');
            this._setDescription(
                '.admin__field[class*="field-chooseroptions_"] button[id*=_product_ids]',
                'Select products. Will be used only with Manual Product block.');
            $('option', this.multiselect).mousedown(function(e, triggered) {
                e.preventDefault();
                if(typeof triggered === 'undefined'){
                    $(this).prop('selected', !$(this).prop('selected'));
                }
                widget._loadData();
                widget._setData();
                widget._onChangeTitle();
                widget._labelsDepend();
                widget._disableCategories();
                widget._manualProducts(this);
                return false;
            }).trigger('mousedown', [true]);

            // Label fields custom dependence
            $("[id$='_show_label']", widget.element).on("change", this._labelsDepend).trigger("change");
            $("[id$='_is_banner']", widget.element).on("change", this._bannerContentDepend).trigger("change");
            $("[id$='_show_carousel']", widget.element).on("change", this._arrowsCssDepend).trigger("change");
        },

        _loadData: function () {
            this._generateFields(this._getMap());
        },

        _setData: function () {
            var data = {};
            var field = '';
            $('.featured_product_field', this.fieldset).each(function (index, element) {
                if(typeof (data[$(element).attr('name')]) == 'undefined'){
                    data[$(element).attr('name')] = {};
                }
                field = $(element).hasClass('data_title')
                    ? 'title'
                    : ($(element).hasClass('data_label')
                        ? 'label'
                        : '');
                data[$(element).attr('name')][field] = $(element).val();
            });

            data = JSON.stringify(data);
            if(data.indexOf('}}') + 1) {
                data = data.substring(0, data.length - 1) + ' }';
            }
            data = data.replace(/"/g, "`");

            $('input[id$="_json_data"]', this.fieldset).val(data);
        },

        _getData: function () {
            var inputVal = $('input[id$="_json_data"]', this.fieldset).val();

            if(inputVal.indexOf('} }') + 1) {
                inputVal = inputVal.substring(0, inputVal.length - 2) + '}';
            }
            inputVal = inputVal.replace(/`/g, "\"");
            inputVal = inputVal.replace(/'/g, "\"");


            return inputVal ? $.parseJSON(inputVal) : false;
        },

        _onChangeTitle: function () {
            $('.featured_product_field', this.fieldset).on('change', this._setData);
        },

        _labelsDepend: function () {
            var labelField = $('.type_label', this.fieldset);
            $('option', "select[id$='_show_label']").prop('selected') ? labelField.show() : labelField.hide()
        },

        _bannerContentDepend: function () {
            var bannerContentField = $('div[class*="_banner_content"]', this.fieldset);
            $('option', "select[id$='_is_banner']").prop('selected') ? bannerContentField.show() : bannerContentField.hide()
        },

        _arrowsCssDepend: function () {
            var prevButton = $('div[class*="_prev_button"]', this.fieldset);
            var nextButton = $('div[class*="_next_button"]', this.fieldset);
            if($('option', this).prop('selected')){
                if($("option", "select[id$='_use_arrows']", this.fieldset).prop('selected')){
                    prevButton.show();
                    nextButton.show();
                }
            } else {
                prevButton.hide();
                nextButton.hide()
            }
        },

        _getMap: function () {
            var map = {};
            // Get all product types (options within multiselect) and fill array of default values
            $('option', this.multiselect).map(function(){
                map[$(this).val()] = $(this).prop('selected');
                return $(this).val();
            }).get();

            return map;
        },

        _generateFields: function(map){
            var widget = this;
            var data = widget._getData();
            var dataType = '';
            // var multiselectBlock = this.multiselectBlock;
            var target = $('.admin__field.field[class*=categories]');


            //Check whether the field is available. Add or remove fields.
            $.each(map, function (type, available) {
                if(available) {
                    if(!$('.admin__field.field.'+type).length){
                        dataType = data[type] ? data[type] : false;
                        target.after(widget._getTemplate(type, dataType, 'title'));
                        $('div.admin__field[class*="_show_label"]', this.fieldset).after(widget._getTemplate(type, dataType, 'label'));
                    }
                } else {
                    $('.admin__field.field.'+type).remove();
                }
            });
        },

        _disableCategories: function (){
            var categories = $('.admin__field[class*="_categories"]');

            // TODO temporary solution
            var ruleTree = $('.rule-tree-wrapper', categories);
            ruleTree.children().not('ul.rule-param-children').remove();
            ruleTree.html($('ul.rule-param-children', ruleTree));
            //
            var select = $('#conditions__1__new_child', ruleTree);
            $('option:not([value*="category_ids"])', select).hide();
            // endTODO
        },

        _manualProducts: function (options) {
            $(options).each(function () {
                if($(this).val() == 'manual_product'){
                    var chooser = $('.admin__field[class*="_product_ids"]');
                    if($(this).prop('selected')) {
                        chooser.show();
                    } else {
                        chooser.hide();
                    }
                }
            });
        },

        _setDescription: function (selector, description) {
            $(selector).after(
                '<div class="note admin__field-note">'+description+'</div>'
            );
        },

        _getTemplate: function (type, title, field) {
            var template = mageTemplate(this.options.template);
            var data = {};
            var option = $('option[value='+type+']', this.multiselect);
            data.title = option.length ? option.text() : title[field];
            data.type = type;
            data.field = field;
            data.value = title ? title[field] : option.text().replace(' Products', '');
            return template({
                data: data
            });
        }

    });

    return $.TemplateMonster.featuredProductWidget;

});
