define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'jquery',
    'ko',
    'underscore',
    'sidebar',
    'mage/translate'
], function (Component, customerData, $, ko, _) {
    'use strict';

    var sidebarInitialized = false,
        addToCartCalls = 0,
        miniCart;

    miniCart = $('[data-block=\'minicart\']');

    /**
     * @return {Boolean}
     */
    function initSidebarExtend () {
        if (miniCart.data('mageSidebar')) {
            miniCart.sidebar('update');
        }

        if (!$('[data-role=product-item]').length) {
            return false;
        }
        miniCart.trigger('contentUpdated');
        $(".details-qty.qty input[type='number']").change( function() {
            $(".update-cart-item").css({'display':'block'})
        });

        if (sidebarInitialized) {
            return false;
        }
        sidebarInitialized = true;
        miniCart.sidebar({
            'targetElement': 'div.block.block-minicart',
            'url': {
                'checkout': window.checkout.checkoutUrl,
                'update': window.checkout.updateItemQtyUrl,
                'remove': window.checkout.removeItemUrl,
                'loginUrl': window.checkout.customerLoginUrl,
                'isRedirectRequired': window.checkout.isRedirectRequired
            },
            'button': {
                'checkout': '#top-cart-btn-checkout',
                'remove': '#mini-cart a.action.delete',
                'close': '#btn-minicart-close'
            },
            'showcart': {
                'parent': 'span.counter',
                'qty': 'span.counter-number',
                'label': 'span.counter-label'
            },
            'minicart': {
                'list': '#mini-cart',
                'content': '#minicart-content-wrapper',
                'qty': 'div.items-total',
                'subtotal': 'div.subtotal span.price',
                'maxItemsVisible': window.checkout.minicartMaxItemsVisible
            },
            'item': {
                'qty': ':input.cart-item-qty',
                'button': ':button.update-cart-item'
            },
            'confirmMessage': $.mage.__('Are you sure you would like to remove this item from the shopping cart?')
        });
    }


    miniCart.on('dropdowndialogopen', function () {
        initSidebarExtend();
    });
    return function (Component) {
        return Component.extend({
            /**
             * @override
             */
            initialize: function () {
                var self = this,
                    cartData = customerData.get('cart');

                this.update(cartData());
                cartData.subscribe(function (updatedCart) {
                    addToCartCalls--;
                    this.isLoading(addToCartCalls > 0);
                    sidebarInitialized = false;
                    this.update(updatedCart);
                    initSidebarExtend();
                }, this);
                $('[data-block="minicart"]').on('contentLoading', function () {
                    addToCartCalls++;
                    self.isLoading(true);
                });

                if (cartData()['website_id'] !== window.checkout.websiteId) {
                    customerData.reload(['cart'], false);
                }

                return this._super();
            },
            isLoading: ko.observable(false),
            initSidebar: initSidebarExtend,

        });
    };

});
