define(
    [
        'ko',
        'uiComponent',
        'Cryozonic_StripePayments/js/view/payment/method-renderer/cryozonic_stripe'
    ],
    function (
        ko,
        Component,
        paymentMethod
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                // template: 'Cryozonic_StripePayments/payment/apple_pay_top',
                cryozonicStripeShowApplePaySection: false,
                cryozonicApplePayToken: null
            },

            initObservable: function ()
            {
                this._super()
                    .observe([
                        'cryozonicStripeJsToken',
                        'cryozonicApplePayToken',
                        'cryozonicStripeShowApplePaySection',
                        'isPaymentRequestAPISupported'
                    ]);

                this.securityMethod = this.config().securityMethod;

                var self = this;

                if (typeof onPaymentSupportedCallbacks == 'undefined')
                    window.onPaymentSupportedCallbacks = [];

                onPaymentSupportedCallbacks.push(function()
                {
                    self.isPaymentRequestAPISupported(true);
                    self.cryozonicStripeShowApplePaySection(true);
                });

                if (typeof onTokenCreatedCallbacks == 'undefined')
                    window.onTokenCreatedCallbacks = [];

                onTokenCreatedCallbacks.push(function(token)
                {
                    self.cryozonicStripeJsToken(token.id + ':' + token.card.brand + ':' + token.card.last4);
                    self.setApplePayToken(token);
                });

                this.displayAtThisLocation = ko.computed(function()
                {
                    return paymentMethod.prototype.config().applePayLocation == 2 &&
                        paymentMethod.prototype.config().enabled;
                }, this);

                return this;
            },

            showApplePaySection: function()
            {
                return this.isPaymentRequestAPISupported;
            },

            setApplePayToken: function(token)
            {
                this.cryozonicApplePayToken(token);
            },

            resetApplePay: function()
            {
                this.cryozonicApplePayToken(null);
                this.cryozonicStripeJsToken(null);
            },

            showApplePayButton: function()
            {
                return !this.isPaymentRequestAPISupported;
            },

            config: function()
            {
                return paymentMethod.prototype.config();
            },

            beginApplePay: function()
            {
                paymentMethod.prototype.beginApplePay();
            }

        });
    }
);
