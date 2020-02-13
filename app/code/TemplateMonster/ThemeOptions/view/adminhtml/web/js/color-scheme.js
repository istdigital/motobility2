define([
    'jquery',
    'underscore'
], function ($, _) {
    'use strict';

    $.widget('tm.colorScheme', {

        options: {
            fieldPrefix: /^theme_options_color_settings_/,
            defaultValues: {},
            userValues: {}
        },

        fieldset: null,
        preview: null,

        _create: function() {
            this.fieldset = $(this.element).closest('fieldset');
            this.preview = $(this.element).next('.preview');
            _.bindAll(this, '_onSchemeChangeHandler', '_onUseThemeValueHandler', '_userFieldPredicate');
            this._bind();
        },

        _bind: function() {
            $(this.element).change(this._onSchemeChangeHandler).change();
            $('.use-default :checkbox', this.fieldset).click(this._onUseThemeValueHandler);
        },

        _onSchemeChangeHandler: function(event) {
            var theme = $(this.element).val();
            var image = '#' + theme;

            // show preview for current color scheme, hide others
            $(this.preview).find('img').hide().filter(image).show();

            this._updateAllValues(theme);
        },

        _onUseThemeValueHandler: function(event) {
            var theme = $(this.element).val();
            var field = $(event.currentTarget).closest('tr').find('.value input');
            var isDefault = $(event.currentTarget).is(':checked');
            var value = isDefault ? this._getDefaultValue(theme, field) : this._getUserValue(theme, field);

            this._updateValue(field, value, isDefault);
        },

        _updateAllValues: function (theme) {
            var fields = $('.value > input', this.fieldset);
            var predicate = _.partial(this._userFieldPredicate, theme);

            // loop through user fields
            fields.filter(predicate).each(_.bind(function (index, field) {
                var value = this._getUserValue(theme, field);
                this._updateValue(field, value);
            }, this));

            // loop through default fields
            fields.filter(_.negate(predicate)).each(_.bind(function (index, field) {
                var value = this._getDefaultValue(theme, field);
                this._updateValue(field, value, true);
            }, this));
        },

        _updateValue: function(field, value, isDefault) {
            $(field).val(value).prop('disabled', isDefault ? true : null).trigger('change');
            $(field).closest('tr').find(':checkbox').prop('checked', isDefault ? true : null);
        },

        _userFieldPredicate: function (theme, i, field) {
            var name = this._getParamName(field);

            return _.has(_.property(theme)(this.options.userValues), name);
        },

        _getUserValue: function(theme, field) {
            return this._getValue(theme, field)(this.options.userValues);
        },

        _getDefaultValue: function(theme, field) {
            return this._getValue(theme, field)(this.options.defaultValues);
        },

        _getValue: function(theme, field) {
            var name = this._getParamName(field);

            return _.compose(_.property(name), _.property(theme));
        },

        _getParamName: function(field) {
            return $(field).attr('id').replace(this.options.fieldPrefix, '');
        }

    });

    return $.tm.colorScheme;
});