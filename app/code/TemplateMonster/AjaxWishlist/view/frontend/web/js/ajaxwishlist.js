define([
    'jquery',
    'mage/template',
    'Magento_Ui/js/modal/alert',
    'jquery/ui',
    'mage/translate',
    'mage/loader'
], function($, mageTemplate, alert) {
    'use strict';

    $.widget('tm.ajaxWishlist', {

        options: {
            addToWishlistSelector: '[data-action="add-to-wishlist"]',
            removeFromWishlistSelector: '.block-wishlist .btn-remove',
            wishlistBlockSelector: '#wishlist-view-form',
            formKeyInputSelector: 'input[name="form_key"]',
            notLoggedInLink: '<a href="<%- url %>"> ' + $.mage.__('logged in.') + '</a>',
            notLoggedInErrorMessage: $.mage.__('To add the product to Wishlist you must be'),
            errorMessage: $.mage.__('There is an error occurred while processing the request.'),
            isShowSpinner: true,
            isShowSuccessMessage: true,
            customerLoginUrl: null
        },

        _create: function() {
            this._bind();
        },
        
        _bind: function () {
            var selectors = [
                this.options.addToWishlistSelector,
                this.options.removeFromWishlistSelector
            ];

            $('body').on('click', selectors.join(','), $.proxy(this._processViaAjax, this));
        },

        _processViaAjax: function(event) {
            var
                post = $(event.currentTarget).data('post'),
                url = post.action,
                data = $.extend(post.data, {form_key: $(this.options.formKeyInputSelector).val()});

            $.ajax(url, {
                method: 'POST',
                data: data,
                showLoader: this.options.isShowSpinner
            }).done($.proxy(this._successHandler, this)).fail($.proxy(this._errorHandler, this));

            event.stopPropagation();
            event.preventDefault();
        },

        _successHandler: function(data) {
            if (!data.success && data.error == 'not_logged_in') {
                alert({
                    title: $.mage.__('Error!'),
                    content: mageTemplate(this.options.notLoggedInErrorMessage + this.options.notLoggedInLink, {
                        url: this.options.customerLoginUrl
                    })
                });

                return;
            }

            $(this.options.wishlistBlockSelector).replaceWith(data.wishlist);
            $('body').trigger('contentUpdated');

            if (this.options.isShowSuccessMessage && data.message) {
                alert({
                    title: $.mage.__('Congratulation!'),
                    content: data.message
                });
            }
        },

        _errorHandler: function () {
            alert({
                title: $.mage.__('Error!'),
                content: mageTemplate(this.options.errorMessage)
            });
        }

    });

    return $.tm.ajaxWishlist;

});