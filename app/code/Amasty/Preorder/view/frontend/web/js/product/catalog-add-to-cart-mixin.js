define([
    'jquery',
    'mage/translate'
], function($, $t) {
    "use strict";

    return function (widget) {

        $.widget('mage.catalogAddToCart', widget, {
            disableAddToCartButton: function(form) {
                this.options.addToCartButtonTextDefault =
                    $(form).find(this.options.addToCartButtonSelector).find('span').first().text();
                this._super(form);
            }
        });

        return $.mage.catalogAddToCart;
    }
});
