define(
    [
        'ko',
        'jquery',
        'uiComponent',
        'StripeIntegration_Payments/js/view/payment/method-renderer/stripe_payments',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_CheckoutAgreements/js/model/agreement-validator',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/quote'
    ],
    function (
        ko,
        $,
        Component,
        paymentMethod,
        additionalValidators,
        agreementValidator,
        selectPaymentMethod,
        checkoutData,
        quote
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                // template: 'StripeIntegration_Payments/payment/apple_pay_top',
                stripePaymentsShowApplePaySection: false,
                stripePaymentsApplePayToken: null
            },

            initObservable: function ()
            {
                this._super()
                    .observe([
                        'stripePaymentsStripeJsToken',
                        'stripePaymentsApplePayToken',
                        'stripePaymentsShowApplePaySection',
                        'isPaymentRequestAPISupported'
                    ]);

                this.securityMethod = this.config().securityMethod;

                var self = this;

                if (typeof onPaymentSupportedCallbacks == 'undefined')
                    window.onPaymentSupportedCallbacks = [];

                onPaymentSupportedCallbacks.push(function()
                {
                    self.isPaymentRequestAPISupported(true);
                    self.stripePaymentsShowApplePaySection(true);
                    stripe.prButton.on('click', self.beginApplePay.bind(self));
                });

                if (typeof onTokenCreatedCallbacks == 'undefined')
                    window.onTokenCreatedCallbacks = [];

                onTokenCreatedCallbacks.push(function(token)
                {
                    self.stripePaymentsStripeJsToken(token.id + ':' + token.card.brand + ':' + token.card.last4);
                    self.setApplePayToken(token);
                });

                this.displayAtThisLocation = ko.computed(function()
                {
                    return paymentMethod.prototype.config().applePayLocation == 2 &&
                        paymentMethod.prototype.config().enabled;
                }, this);

                quote.paymentMethod.subscribe(function(method)
                {
                    if (method != null)
                    {
                        $(".payment-method.stripe-payments.mobile").removeClass("_active");
                    }
                }
                , null, 'change');

                return this;
            },

            showApplePaySection: function()
            {
                return this.isPaymentRequestAPISupported;
            },

            setApplePayToken: function(token)
            {
                this.stripePaymentsApplePayToken(token);
            },

            resetApplePay: function()
            {
                this.stripePaymentsApplePayToken(null);
                this.stripePaymentsStripeJsToken(null);
            },

            showApplePayButton: function()
            {
                return !this.isPaymentRequestAPISupported;
            },

            config: function()
            {
                return paymentMethod.prototype.config();
            },

            beginApplePay: function(e)
            {
                this.makeActive();
                if (!this.validate())
                {
                    e.preventDefault();
                }
            },

            makeActive: function()
            {
                if (!this.displayAtThisLocation())
                    return;

                // If there are any selected payment methods from a different section, make them inactive
                // This ensures that their form validations will not run
                try
                {
                    if (checkoutData.getSelectedPaymentMethod())
                        selectPaymentMethod(null);
                }
                catch (e) {}

                // We do want terms & conditions validation for Apple Pay, so activate that temporarily
                $(".payment-method.stripe-payments.mobile").addClass("_active");
            },

            validate: function(region)
            {
                return agreementValidator.validate() && additionalValidators.validate();
            }

        });
    }
);
