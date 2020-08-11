define([
    'jquery',
    'Magento_Catalog/js/catalog-add-to-cart'
], function($) {
    'use strict';

    $.widget('mage.amastyPreorder', {
       options: {
           addToCartButton: $("#product-addtocart-button span"),
           availabilityElement: '',
           preOrderNote: '',
           addToCartLabel: '',
           originalNote: ''
       },

        _original: {
            availabilityText: '',
            addToCartLabel: ''
        },

        _enabled: false,

        _create: function() {
            this._saveOriginal();
        },

        _saveOriginal: function () {
            this.options.availabilityElement = this.options.availabilityElement
                ? $(this.options.availabilityElement)
                : $(".product-info-main").find('.stock');
            if (this.options.availabilityElement) {
                this._original.availabilityText = this.options.originalNote
                    ? this.options.originalNote
                    : this.options.availabilityElement.text();
            }

            if (this.options.addToCartButton.length) {
                var originalButtonElement = $('.original-add-to-cart-text');
                if (originalButtonElement.length > 0) {
                    this._original.addToCartLabelText = originalButtonElement.data('text');
                    originalButtonElement.remove();
                } else {
                    this._original.addToCartLabelText = this.options.addToCartButton.text();
                }
            }
        },

        _changeLabels: function() {
            this._changeAvailability();
            this.options.addToCartButton.html(this.options.addToCartLabel);
        },

        _changeAvailability: function () {
            if (this.options.availabilityElement && this.options.preOrderNote) {
                var additionalAvailability = '';
                if (this.options.availabilityElement.find('.amstockstatus')) {
                    additionalAvailability = '<br>' + $('<div>').append(
                            this.options.availabilityElement.find('.amstockstatus').clone()
                        ).html();
                }
                this.options.availabilityElement.html(this.options.preOrderNote + additionalAvailability);
                this.options.availabilityElement.addClass('ampreorder-observed');
            }
        },

        enable: function() {
            /*if(this._enabled) {
                return;
            }*/
            this._enabled = true;
            this._changeLabels();
        },

        disable: function() {
            /*if(!this._enabled) {
                return;
            }*/
            this._enabled = false;
            if (this.options.availabilityElement){
                this.options.availabilityElement.text(this._original.availabilityText);
                this.options.availabilityElement.removeClass('ampreorder-observed');
            }
            this.options.addToCartButton.text(this._original.addToCartLabelText);
        }
    });

    return $.mage.amastyPreorder;
});
