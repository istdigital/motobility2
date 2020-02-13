define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'TemplateMonster_AjaxWishlist/js/ajaxwishlist',
    'jquery/ui',
    'mage/translate',
    'Magento_Wishlist/js/wishlist',
    'mage/loader'
], function($, alert) {
    'use strict';

    if ($('body').data('tmAjaxWishlist')) {

        $.widget('tm.wishlist', $.mage.wishlist, {
            options: {
                btnUpdateSelector: '.update',
                formKeyInputSelector: 'input[name="form_key"]',
                errorMessage: $.mage.__('There is an error occurred while processing the request.')
            },

            _create: function () {
                this._super();
                this._bind();
            },

            _bind: function () {
                this.element
                    .off('click', this.options.btnRemoveSelector) // remove parent widget's handlers
                    .on('click', this.options.btnRemoveSelector, $.proxy(this._remove, this))
                    .on('click', this.options.btnUpdateSelector, $.proxy(this._update, this));
            },

            _remove: function (event) {
                var
                    params = $(event.currentTarget).data('post-remove'),
                    url = params.action,
                    data = $.extend(params.data, {form_key: $(this.options.formKeyInputSelector).val()});

                this._processAjaxRequest(url, data);

                event.stopPropagation();
                event.preventDefault();
            },

            _update: function (event) {
                var
                    form = $(event.currentTarget).closest('form'),
                    url = $(form).attr('action'),
                    data = $(form).serialize();

                this._processAjaxRequest(url, data);

                event.stopPropagation();
                event.preventDefault();
            },

            _processAjaxRequest: function (url, data) {
                $.ajax(url, {
                    method: 'POST',
                    data: data,
                    showLoader: $('body').ajaxWishlist('option', 'isShowSpinner')
                }).done($.proxy(this._successHandler, this)).fail($.proxy(this._errorHandler, this));
            },

            _successHandler: function (data) {
                $(this.element).replaceWith(data.wishlist);
                $('body').trigger('contentUpdated');
            },

            _errorHandler: function () {
                alert({
                    content: this.options.errorMessage
                });
            }

        });

    }

});