/*jshint browser:true jquery:true*/
/*global define*/

// Workaround: Uncaught TypeError: Cannot read property 'storeCode' of undefined
if (typeof window.checkoutConfig === 'undefined') {
    window.checkoutConfig = {
        storeCode: 'default'
    };
}

define(
    [
        'jquery',
        'mage/url',
        'Magento_Catalog/product/view/validation',
        'mage/storage',
        'Magento_Ui/js/modal/alert',
        'mage/translate',
        'stripe_payments'
    ],
    function (jQuery, urlBuilder, validation, storage, alert, $t) {
        'use strict';

        return {
            getApplePayParams: function(type, callback)
            {
                var serviceUrl = urlBuilder.build('/rest/V1/stripe/payments/get_prapi_params', {}),
                    payload = {type: type},
                    self = this;

                return storage.post(
                    serviceUrl,
                    JSON.stringify(payload),
                    false
                )
                .fail(function (xhr, textStatus, errorThrown)
                {
                    console.error("Could not retrieve initialization params for Apple Pay");
                })
                .done(function (response)
                {
                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }

                    callback(response);
                });
            },
            /**
             * Init Stripe Express
             * @param element_id
             * @param apiKey
             * @param paramsType
             * @param settings
             * @param callback
             */
            initStripeExpress: function (element_id, apiKey, paramsType, settings, callback)
            {
                stripe.securityMethod = 2;
                stripe.apiKey = apiKey;
                var self = this;

                this.getApplePayParams(paramsType, function(params)
                {
                    if (!params || params.length == 0)
                        return;

                    if (stripe.stripeJsV3)
                        self.onStripeJsLoaded(element_id, apiKey, params, settings, callback);
                    else
                    {
                        stripe.loadStripeJsV3(function () {
                            self.onStripeJsLoaded(element_id, apiKey, params, settings, callback);
                        });
                    }
                });
            },

            onStripeJsLoaded: function(element_id, apiKey, params, settings, callback)
            {
                if (!stripe.stripeJsV3) {
                    stripe.stripeJsV3 = Stripe(apiKey);
                }

                // Init Payment Request
                var paymentRequest,
                    paymentRequestButton = jQuery(element_id);

                try {
                    paymentRequest = stripe.stripeJsV3.paymentRequest(params);
                    var elements = stripe.stripeJsV3.elements();
                    var prButton = elements.create('paymentRequestButton', {
                        paymentRequest: paymentRequest,
                        style: {
                            paymentRequestButton: {
                                type: settings.type,
                                theme: settings.theme,
                                height: settings.height + 'px'
                            }
                        }
                    });
                } catch (e) {
                    console.warn(e.message);
                    return;
                }

                paymentRequest.canMakePayment().then(function(result) {
                    stripe.canMakePaymentResult = result;
                    if (result)
                    {
                        // The minicart may be empty
                        if (document.getElementById(element_id.substr(1)))
                            prButton.mount(element_id);
                    }
                    else {
                        paymentRequestButton.hide();
                    }
                });

                prButton.on('ready', function () {
                    callback(paymentRequestButton, paymentRequest, params, prButton);
                });
            },

            /**
             * Place Order
             * @param result
             * @param callback
             */
            placeOrder: function (result, callback) {
                var serviceUrl = urlBuilder.build('/rest/V1/stripe/payments/place_order', {}),
                    payload = {result: result},
                    self = this;

                return storage.post(
                    serviceUrl,
                    JSON.stringify(payload),
                    false
                ).fail(function (xhr, textStatus, errorThrown) {
                    var response = JSON.parse(xhr.responseText);

                    if (stripe.isAuthenticationRequired(response.message))
                    {
                        return stripe.processNextAuthentication(function(err)
                        {
                            if (err)
                                return callback(err, { message: err }, result);

                            self.placeOrder(result, callback);
                        });
                    }
                    else
                        callback(response.message, response, result);
                }).done(function (response) {
                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }

                    callback(null, response, result);
                });
            },

            /**
             * Add Item to Cart
             * @param request
             * @param shipping_id
             * @param callback
             */
            addToCart: function(request, shipping_id, callback)
            {
                var serviceUrl = urlBuilder.build('/rest/V1/stripe/payments/addtocart', {}),
                    payload = {request: request, shipping_id: shipping_id},
                    self = this;

                return storage.post(
                    serviceUrl,
                    JSON.stringify(payload),
                    false
                ).fail(function (xhr, textStatus, errorThrown) {
                    var response = JSON.parse(xhr.responseText);
                    callback(response.message, response);
                }).done(function (response) {
                    self.processResponseWithPaymentIntent(response, callback);
                });
            },

            /**
             * Get Cart Contents
             * @param callback
             * @returns {*}
             */
            getCart: function(callback) {
                var serviceUrl = urlBuilder.build('/rest/V1/stripe/payments/get_cart', {});

                return storage.get(
                    serviceUrl,
                    null,
                    false
                ).fail(function (xhr, textStatus, errorThrown) {
                    var response = JSON.parse(xhr.responseText);
                    callback(response.message, response);
                }).done(function (response) {
                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }

                    callback(null, response);
                });
            },

            /**
             * Estimate Shipping for Cart
             * @param address
             * @param callback
             * @returns {*}
             */
            estimateShippingCart: function(address, callback) {
                var serviceUrl = urlBuilder.build('/rest/V1/stripe/payments/estimate_cart', {}),
                    payload = {address: address},
                    self = this;

                return storage.post(
                    serviceUrl,
                    JSON.stringify(payload),
                    false
                ).fail(function (xhr, textStatus, errorThrown) {
                    var response = JSON.parse(xhr.responseText);
                    callback(response.message, response);
                }).done(function (response) {
                    self.processResponseWithPaymentIntent(response, callback);
                });
            },

            setBillingAddress: function(data, callback) {
                var serviceUrl = urlBuilder.build('/rest/V1/stripe/payments/set_billing_address', {}),
                    payload = {data: data},
                    self = this;

                return storage.post(
                    serviceUrl,
                    JSON.stringify(payload),
                    false
                ).fail(function (xhr, textStatus, errorThrown) {
                    var response = JSON.parse(xhr.responseText);
                    callback(response.message, response);
                }).done(function (response) {
                    self.processResponseWithPaymentIntent(response, callback);
                });
            },

            /**
             * Apply Shipping and Return Totals
             * @param address
             * @param shipping_id
             * @param callback
             * @returns {*}
             */
            applyShipping: function(address, shipping_id, callback) {
                var serviceUrl = urlBuilder.build('/rest/V1/stripe/payments/apply_shipping', {}),
                    payload = {address: address, shipping_id: shipping_id},
                    self = this;

                return storage.post(
                    serviceUrl,
                    JSON.stringify(payload),
                    false
                ).fail(function (xhr, textStatus, errorThrown) {
                    var response = JSON.parse(xhr.responseText);
                    callback(response.message, response);
                }).done(function (response) {
                    self.processResponseWithPaymentIntent(response, callback);
                });
            },

            processResponseWithPaymentIntent: function(response, callback)
            {
                try
                {
                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }

                    if (response.paymentIntent)
                        stripe.paymentIntent = response.paymentIntent;

                    callback(null, response.results);
                }
                catch (e)
                {
                    callback("Received invalid response from the Web API", response);
                }
            },

            /**
             * Init Widget for Cart Page
             * @param paymentRequestButton
             * @param paymentRequest
             * @param params
             * @param prButton
             */
            initCartWidget: function (paymentRequestButton, paymentRequest, params, prButton) {
                var self = this,
                    shippingAddress = [],
                    shippingMethod = null;

                paymentRequest.on('shippingaddresschange', function(ev) {
                    shippingAddress = ev.shippingAddress;
                    self.estimateShippingCart(shippingAddress, function (err, shippingOptions) {
                        if (err) {
                            ev.updateWith({status: 'invalid_shipping_address'});
                            return;
                        }

                        if (shippingOptions.length < 1) {
                            ev.updateWith({status: 'invalid_shipping_address'});
                            return;
                        }

                        shippingMethod = null;
                        if (shippingOptions.length > 0) {
                            // Apply first shipping method
                            var shippingOption = shippingOptions[0];
                            shippingMethod = shippingOption.hasOwnProperty('id') ? shippingOption.id : null;
                        }

                        self.applyShipping(shippingAddress, shippingMethod, function (err, response) {
                            if (err) {
                                ev.updateWith({status: 'fail'});
                                return;
                            }

                            // Update order lines
                            var result = Object.assign({status: 'success', shippingOptions: shippingOptions}, response);
                            ev.updateWith(result);
                        });
                    });
                });

                paymentRequest.on('shippingoptionchange', function(ev) {
                    var shippingMethod = ev.shippingOption.hasOwnProperty('id') ? ev.shippingOption.id : null;
                    self.applyShipping(shippingAddress, shippingMethod, function (err, response) {
                        if (err) {
                            ev.updateWith({status: 'fail'});
                            return;
                        }

                        // Update order lines
                        var result = Object.assign({status: 'success'}, response);
                        ev.updateWith(result);
                    });
                });

                var self = this;
                paymentRequest.on('paymentmethod', function(result)
                {
                    self.onPaymentRequestPaymentMethod.call(self, result, paymentRequestButton);
                });
            },

            /**
             * Init Widget for MiniCart
             * @param paymentRequestButton
             * @param paymentRequest
             * @param params
             * @param prButton
             */
            initMiniCartWidget: function (paymentRequestButton, paymentRequest, params, prButton) {
                var self = this,
                    shippingAddress = [],
                    shippingMethod = null;

                prButton.on('click', function(ev) {
                    // ev.preventDefault();

                    paymentRequestButton.addClass('disabled');
                    self.getCart(function (err, result) {
                        paymentRequestButton.removeClass('disabled');
                        if (err) {
                            console.warn(err);
                            // @todo Fix it: Already called show() once.
                            // paymentRequest.show();
                            return;
                        }

                        // ev.updateWith(result);
                    });
                });

                paymentRequest.on('shippingaddresschange', function(ev) {
                    shippingAddress = ev.shippingAddress;
                    self.estimateShippingCart(shippingAddress, function (err, shippingOptions) {
                        if (err) {
                            ev.updateWith({status: 'invalid_shipping_address'});
                            return;
                        }

                        if (shippingOptions.length < 1) {
                            ev.updateWith({status: 'invalid_shipping_address'});
                            return;
                        }

                        shippingMethod = null;
                        if (shippingOptions.length > 0) {
                            // Apply first shipping method
                            var shippingOption = shippingOptions[0];
                            shippingMethod = shippingOption.hasOwnProperty('id') ? shippingOption.id : null;
                        }

                        self.applyShipping(shippingAddress, shippingMethod, function (err, response) {
                            if (err) {
                                ev.updateWith({status: 'fail'});
                                return;
                            }

                            // Update order lines
                            var result = Object.assign({status: 'success', shippingOptions: shippingOptions}, response);
                            ev.updateWith(result);
                        });
                    });
                });

                paymentRequest.on('shippingoptionchange', function(ev) {
                    var shippingMethod = ev.shippingOption.hasOwnProperty('id') ? ev.shippingOption.id : null;
                    self.applyShipping(shippingAddress, shippingMethod, function (err, response) {
                        if (err) {
                            ev.updateWith({status: 'fail'});
                            return;
                        }

                        // Update order lines
                        var result = Object.assign({status: 'success'}, response);
                        ev.updateWith(result);
                    });
                });

                var self = this;
                paymentRequest.on('paymentmethod', function(result)
                {
                    self.onPaymentRequestPaymentMethod.call(self, result, paymentRequestButton);
                });
            },

            onPaymentRequestPaymentMethod: function(result, paymentRequestButton)
            {
                stripe.PRAPIEvent = result;
                var success = this.onPaymentPlaced.bind(this, result, paymentRequestButton);
                var error = this.showError.bind(this);

                paymentRequestButton.addClass('disabled');
                this.setBillingAddress(result.paymentMethod.billing_details, function(err, response)
                {
                    paymentRequestButton.removeClass('disabled');
                    if (err) {
                        error(response.message);
                        return;
                    }

                    success();
                });
            },

            showError: function(message)
            {
                if (stripe.PRAPIEvent)
                    stripe.closePaysheet('success'); // Simply hide the modal

                alert({
                    title: $t('Error'),
                    content: message,
                    actions: {
                        always: function (){}
                    }
                });
            },

            onPaymentPlaced: function(result, paymentRequestButton)
            {
                var self = this;
                paymentRequestButton.addClass('disabled');
                this.placeOrder(result, function (err, response, result)
                {
                    paymentRequestButton.removeClass('disabled');
                    if (err)
                        self.showError(response.message);
                    else if (response.hasOwnProperty('redirect'))
                        window.location = response.redirect;
                });
            },

            /**
             * Init Widget for Single Product Page
             * @param paymentRequestButton
             * @param paymentRequest
             * @param params
             * @param prButton
             */
            initProductWidget: function (paymentRequestButton, paymentRequest, params, prButton) {
                var self = this,
                    form = jQuery('#product_addtocart_form'),
                    request = [],
                    shippingAddress = [],
                    shippingMethod = null;

                prButton.on('click', function(ev)
                {
                    var validator = form.validation({radioCheckboxClosest: '.nested'});

                    if (!validator.valid())
                    {
                        ev.preventDefault();
                        return;
                    }

                    // We don't want to preventDefault for applePay because we cannot use
                    // paymentRequest.show() with applePay. Expecting Stripe to fix this.
                    if (!stripe.canMakePaymentResult.applePay)
                        ev.preventDefault();

                    // Add to Cart
                    request = form.serialize();
                    paymentRequestButton.addClass('disabled');
                    self.addToCart(request, shippingMethod, function (err, result) {
                        paymentRequestButton.removeClass('disabled');
                        if (err) {
                            stripe.closePaysheet('success');
                            alert(err);
                            return;
                        }

                        try
                        {
                            paymentRequest.update(result);
                            paymentRequest.show();
                        }
                        catch (e)
                        {
                            console.warn(e.message);
                        }
                    });
                });

                paymentRequest.on('shippingaddresschange', function(ev) {
                    shippingAddress = ev.shippingAddress;
                    self.estimateShippingCart(shippingAddress, function (err, shippingOptions) {
                        if (err) {
                            ev.updateWith({status: 'invalid_shipping_address'});
                            return;
                        }

                        if (shippingOptions.length < 1) {
                            ev.updateWith({status: 'invalid_shipping_address'});
                            return;
                        }

                        shippingMethod = null;
                        if (shippingOptions.length > 0) {
                            // Apply first shipping method
                            var shippingOption = shippingOptions[0];
                            shippingMethod = shippingOption.hasOwnProperty('id') ? shippingOption.id : null;
                        }

                        self.applyShipping(shippingAddress, shippingMethod, function (err, response) {
                            if (err) {
                                ev.updateWith({status: 'fail'});
                                return;
                            }

                            // Update order lines
                            var result = Object.assign({status: 'success', shippingOptions: shippingOptions}, response);
                            ev.updateWith(result);
                        });
                    });
                });

                paymentRequest.on('shippingoptionchange', function(ev) {
                    var shippingMethod = ev.shippingOption.hasOwnProperty('id') ? ev.shippingOption.id : null;
                    self.applyShipping(shippingAddress, shippingMethod, function (err, response) {
                        if (err) {
                            ev.updateWith({status: 'fail'});
                            return;
                        }

                        // Update order lines
                        var result = Object.assign({status: 'success'}, response);
                        ev.updateWith(result);
                    });
                });

                var self = this;
                paymentRequest.on('paymentmethod', function(result)
                {
                    self.onPaymentRequestPaymentMethod.call(self, result, paymentRequestButton);
                });
            }
        };
    }
);
