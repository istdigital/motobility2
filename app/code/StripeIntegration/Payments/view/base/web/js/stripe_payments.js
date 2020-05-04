// Copyright © Stripe, Inc
//
// @package    StripeIntegration_Payments
// @version    1.8.0

var stripeTokens = {};

var initStripe = function(params, callback)
{
    if (typeof callback == "undefined")
        callback = function() {};

    stripe.apiKey = params.apiKey;
    stripe.locale = params.locale;
    stripe.onStripeInitCallback = callback;

    if (stripe.stripeJsV3)
        stripe.onLoadStripeJsV3();
    else
        stripe.loadStripeJsV3(stripe.onLoadStripeJsV3);

    // Disable server side card validation when Stripe.js is enabled
    if (typeof AdminOrder != 'undefined' && AdminOrder.prototype.loadArea && typeof AdminOrder.prototype._loadArea == 'undefined')
    {
        AdminOrder.prototype._loadArea = AdminOrder.prototype.loadArea;
        AdminOrder.prototype.loadArea = function(area, indicator, params)
        {
            if (typeof area == "object" && area.indexOf('card_validation') >= 0)
                area = area.splice(area.indexOf('card_validation'), 0);

            if (area.length > 0)
                this._loadArea(area, indicator, params);
        };
    }
};

// Global Namespace
var stripe =
{
    // Properties
    version: "1.8.0",
    quote: null, // Comes from the checkout js
    customer: null, // Comes from the checkout js
    onPaymentSupportedCallbacks: [],
    onTokenCreatedCallbacks: [],
    paramsApplePay: null, // Comes from the checkout js
    multiShippingFormInitialized: false,
    applePayButton: null,
    applePaySuccess: false,
    applePayResponse: null,
    locale: 'auto',
    card: null,
    stripeJsV2: null,
    stripeJsV3: null,
    apiKey: null,
    avsFields: null,
    isCreatingToken: false,
    multiShippingForm: null,
    multiShippingFormSubmitButton: null,
    token: null,
    sourceId: null,
    response: null,
    iconsContainer: null,
    paymentIntent: null,
    paymentIntents: [],
    concludedPaymentIntents: [],
    isAdmin: false,
    urlBuilder: null,
    storage: null,
    prButton: null,
    onStripeInitCallback: function() {},

    // Methods
    loadStripeJsV3: function(callback)
    {
        var script = document.getElementsByTagName('script')[0];
        var stripeJsV3 = document.createElement('script');
        stripeJsV3.src = "https://js.stripe.com/v3/";
        stripeJsV3.onload = function()
        {
            stripe.onLoadStripeJsV3();
            if (typeof callback === 'function') {
                callback();
            }
        };
        stripeJsV3.onerror = function(evt) {
            console.warn("Stripe.js v3 could not be loaded");
            console.error(evt);
        };
        // Do this on the next cycle so that stripe.onLoadStripeJsV2() finishes first
        script.parentNode.insertBefore(stripeJsV3, script);
    },
    onLoadStripeJsV3: function()
    {
        if (!stripe.stripeJsV3)
        {
            try
            {
                var params = {
                    betas: ['payment_intent_beta_3']
                };
                stripe.stripeJsV3 = Stripe(stripe.apiKey, params);
                stripe.onStripeInit();
            }
            catch (e)
            {
                if (typeof e != "undefined" && typeof e.message != "undefined")
                    return stripe.onStripeInit('Could not initialize Stripe.js: ' + e.message);

                return stripe.onStripeInit('Could not initialize Stripe.js');
            }
        }

        stripe.initLoadedStripeJsV3();
    },
    onStripeInit: function(err)
    {
        if (stripe.stripePaymentForm)
        {
            // We are at the checkout
            stripe.stripePaymentForm.onStripeInit(err);
        }
        else if (typeof err == 'string' && err.length > 0)
        {
            // We are at the customer account section
            stripe.displayCardError(err);
        }

        if (!stripe.stripePaymentForm)
        {
            // We are at the multishipping page
            require(['mage/mage', 'mage/url', 'mage/storage'], function(mage, urlBuilder, storage) {
                stripe.urlBuilder = urlBuilder;
                stripe.storage = storage;
            });
        }

        stripe.onStripeInitCallback();
    },
    initLoadedStripeJsV3: function()
    {
        stripe.initStripeElements();
        stripe.onWindowLoaded(stripe.initStripeElements);

        stripe.initPaymentRequestButton();
        stripe.onWindowLoaded(stripe.initPaymentRequestButton);
    },
    onWindowLoaded: function(callback)
    {
        if (window.attachEvent)
            window.attachEvent("onload", callback); // IE
        else
            window.addEventListener("load", callback); // Other browsers
    },
    getStripeElementsStyle: function()
    {
        // Custom styling can be passed to options when creating an Element.
        return {
            base: {
                // Add your base input styles here. For example:
                fontSize: '16px',
                // lineHeight: '24px'
                // iconColor: '#c4f0ff',
                // color: '#31325F'
        //         fontWeight: 300,
        //         fontFamily: '"Helvetica Neue", Helvetica, sans-serif',

        //         '::placeholder': {
        //             color: '#CFD7E0'
        //         }
            }
        };
    },
    getStripeElementCardNumberOptions: function()
    {
        return {
            // iconStyle: 'solid',
            // hideIcon: false,
            style: stripe.getStripeElementsStyle()
        };
    },
    getStripeElementCardExpiryOptions: function()
    {
        return {
            style: stripe.getStripeElementsStyle()
        };
    },
    getStripeElementCardCvcOptions: function()
    {
        return {
            style: stripe.getStripeElementsStyle()
        };
    },
    getStripeElementsOptions: function()
    {
        return {
            locale: stripe.locale
        };
    },
    initStripeElements: function()
    {
        if (document.getElementById('stripe-payments-card-number') === null)
            return;

        var elements = stripe.stripeJsV3.elements(stripe.getStripeElementsOptions());

        var cardNumber = stripe.card = elements.create('cardNumber', stripe.getStripeElementCardNumberOptions());
        cardNumber.mount('#stripe-payments-card-number');
        cardNumber.addEventListener('change', stripe.stripeElementsOnChange);

        var cardExpiry = elements.create('cardExpiry', stripe.getStripeElementCardExpiryOptions());
        cardExpiry.mount('#stripe-payments-card-expiry');
        cardExpiry.addEventListener('change', stripe.stripeElementsOnChange);

        var cardCvc = elements.create('cardCvc', stripe.getStripeElementCardCvcOptions());
        cardCvc.mount('#stripe-payments-card-cvc');
        cardCvc.addEventListener('change', stripe.stripeElementsOnChange);
    },
    stripeElementsOnChange: function(event)
    {
        if (typeof event.brand != 'undefined')
            stripe.onCardNumberChanged(event.brand);

        if (event.error)
            stripe.displayCardError(event.error.message, true);
        else
            stripe.clearCardErrors();
    },
    onCardNumberChanged: function(cardType)
    {
        stripe.onCardNumberChangedFade(cardType);
        stripe.onCardNumberChangedSwapIcon(cardType);
    },
    resetIconsFade: function()
    {
        stripe.iconsContainer.className = 'input-box';
        var children = stripe.iconsContainer.getElementsByTagName('img');
        for (var i = 0; i < children.length; i++)
            children[i].className = '';
    },
    onCardNumberChangedFade: function(cardType)
    {
        if (!stripe.iconsContainer)
            stripe.iconsContainer = document.getElementById('stripe-payments-accepted-cards');

        if (!stripe.iconsContainer)
            return;

        stripe.resetIconsFade();

        if (!cardType || cardType == "unknown") return;

        var img = document.getElementById('stripe_payments_' + cardType + '_type');
        if (!img) return;

        img.className = 'active';
        stripe.iconsContainer.className = 'input-box stripe-payments-detected';
    },
    cardBrandToPfClass: {
        'visa': 'pf-visa',
        'mastercard': 'pf-mastercard',
        'amex': 'pf-american-express',
        'discover': 'pf-discover',
        'diners': 'pf-diners',
        'jcb': 'pf-jcb',
        'unknown': 'pf-credit-card',
    },
    onCardNumberChangedSwapIcon: function(cardType)
    {
        var brandIconElement = document.getElementById('stripe-payments-brand-icon');
        var pfClass = 'pf-credit-card';
        if (cardType in stripe.cardBrandToPfClass)
            pfClass = stripe.cardBrandToPfClass[cardType];

        for (var i = brandIconElement.classList.length - 1; i >= 0; i--)
            brandIconElement.classList.remove(brandIconElement.classList[i]);

        brandIconElement.classList.add('pf');
        brandIconElement.classList.add(pfClass);
    },
    initPaymentRequestButton: function(onPaymentSupportedCallback, onTokenCreatedCallback)
    {
        if (!stripe.isApplePayEnabled())
            return;

        if (stripe.hasNoCountryCode())
            stripe.paramsApplePay.country = stripe.getCountryCode();

        if (stripe.hasNoCountryCode())
            return;

        var paymentRequest;
        try
        {
            paymentRequest = stripe.stripeJsV3.paymentRequest(stripe.paramsApplePay);
            var elements = stripe.stripeJsV3.elements();
            var prButton = stripe.prButton = elements.create('paymentRequestButton', {
                paymentRequest: paymentRequest,
            });
        }
        catch (e)
        {
            console.warn(e.message);
            return;
        }

        // Check the availability of the Payment Request API first.
        paymentRequest.canMakePayment().then(function(result)
        {
            if (result)
            {
                if (!document.getElementById('payment-request-button'))
                    return;

                prButton.mount('#payment-request-button');

                for (var i = 0; i < onPaymentSupportedCallbacks.length; i++)
                    onPaymentSupportedCallbacks[i]();
            }
        });

        paymentRequest.on('paymentmethod', function(result)
        {
            try
            {
                stripe.PRAPIEvent = result;
                setStripeToken(result.paymentMethod.id, result.paymentMethod);

                for (var i = 0; i < onTokenCreatedCallbacks.length; i++)
                    onTokenCreatedCallbacks[i](result.paymentMethod);

                stripe.closePaysheet('success');

                var messageContainer = stripe.stripePaymentForm.messageContainer;

                // Temporarilly move the error messages to the top of the page and place the order
                if (stripe.stripePaymentForm.config().applePayLocation == 2)
                    stripe.stripePaymentForm.messageContainer = null;

                stripe.stripePaymentForm.placeOrder();
                stripe.stripePaymentForm.messageContainer = messageContainer;
            }
            catch (e)
            {
                stripe.closePaysheet('fail');
                console.error(e);
            }
        });
    },
    isApplePayEnabled: function()
    {
        if (!stripe.paramsApplePay)
            return false;

        return true;
    },
    hasNoCountryCode: function()
    {
        return (typeof stripe.paramsApplePay.country == "undefined" || !stripe.paramsApplePay.country || stripe.paramsApplePay.country.length === 0);
    },
    getCountryElement: function()
    {
        var element = document.getElementById('billing:country_id');

        if (!element)
            element = document.getElementById('billing_country_id');

        if (!element)
        {
            var selects = document.getElementsByName('billing[country_id]');
            if (selects.length > 0)
                element = selects[0];
        }

        return element;
    },
    getCountryCode: function()
    {
        var element = stripe.getCountryElement();

        if (!element)
            return null;

        if (element.value && element.value.length > 0)
            return element.value;

        return null;
    },
    toggleSubscription: function(selector, edit)
    {
        var elements = jQuery(selector);
        if (elements.length === 0) return;

        for (var i = 0; i < elements.length; i++)
        {
            var section = elements[i];
            if (stripe.hasClass(section, 'show'))
            {
                stripe.removeClass(section, 'show');
                if (edit) stripe.removeClass(section, 'edit');
            }
            else
            {
                stripe.addClass(section, 'show');
                if (edit) stripe.addClass(section, 'edit');
            }
        }

        return false;
    },

    editSubscription: function(selector)
    {
        var elements = jQuery(selector);
        if (elements.length === 0) return;

        for (var i = 0; i < elements.length; i++)
        {
            var section = elements[i];
            if (!stripe.hasClass(section, 'edit'))
                stripe.addClass(section, 'edit');
        }
    },

    cancelEditSubscription: function(selector)
    {
        var elements = jQuery(selector);
        if (elements.length === 0) return;

        for (var i = 0; i < elements.length; i++)
        {
            var section = elements[i];
            stripe.removeClass(section, 'edit');
        }
    },

    hasClass: function(element, className)
    {
        return (' ' + element.className + ' ').indexOf(' ' + className + ' ') > -1;
    },

    removeClass: function (element, className)
    {
        if (element.classList)
            element.classList.remove(className);
        else
        {
            var classes = element.className.split(" ");
            classes.splice(classes.indexOf(className), 1);
            element.className = classes.join(" ");
        }
    },

    addClass: function (element, className)
    {
        if (element.classList)
            element.classList.add(className);
        else
            element.className += (' ' + className);
    },

    // Admin

    initRadioButtons: function()
    {
        // Switching between saved cards and new card
        var i, inputs = document.querySelectorAll('#saved-cards input');

        for (i = 0; i < inputs.length; i++)
            inputs[i].onclick = stripe.useCard;

        // Switching between new subscription and switch subsctiption
        inputs = document.querySelectorAll('#payment_form_stripe_payments_subscription input.select.switch');

        for (i = 0; i < inputs.length; i++)
            inputs[i].onclick = stripe.switchSubscription;

        // Selecting a subscription from the dropdown
        var input = $('stripe_payments_select_subscription');
        if (input)
            input.onchange = stripe.switchSubscriptionSelected;
    },

    disableStripeInputValidation: function()
    {
        var i, inputs = document.querySelectorAll(".stripe-input");
        for (i = 0; i < inputs.length; i++)
            $(inputs[i]).removeClassName('required-entry');
    },

    enableStripeInputValidation: function()
    {
        var i, inputs = document.querySelectorAll(".stripe-input");
        for (i = 0; i < inputs.length; i++)
            $(inputs[i]).addClassName('required-entry');
    },

    // Triggered when the user clicks a saved card radio button
    useCard: function(evt)
    {
        var parentId = 'payment_form_stripe_payments_payment';
        if (!$(parentId))
            parentId = 'payment_form_stripe_payments'; // admin area

        // User wants to use a new card
        if (this.value == 'new_card')
        {
            $(parentId).addClassName("stripe-new");
            stripe.enableStripeInputValidation();
            deleteStripeToken();
        }
        // User wants to use a saved card
        else
        {
            $(parentId).removeClassName("stripe-new");
            stripe.disableStripeInputValidation();
            setStripeToken(this.value);
        }

        stripe.sourceId = stripe.cleanToken(stripe.getSelectedSavedCard());
    },
    getSelectedSavedCard: function()
    {
        var elements = document.getElementsByName("payment[cc_saved]");
        if (elements.length == 0)
            return null;

        var selected = null;
        for (var i = 0; i < elements.length; i++)
            if (elements[i].checked)
                selected = elements[i];

        if (!selected)
            return null;

        if (selected.value == 'new_card')
            return null;

        return selected.value;
    },
    switchSubscription: function(evt)
    {
        var newSubscriptionSection = 'payment_form_stripe_payments_payment';
        var existingSubscriptionSection = 'select_subscription';
        var elements = $(existingSubscriptionSection).select( 'select', 'input');

        if (this.value == 'switch')
        {
            $(newSubscriptionSection).addClassName("hide");
            $(existingSubscriptionSection).removeClassName("hide");
            for (var i = 0; i < elements.length; i++)
            {
                if (!stripe.hasClass(elements[i], 'hide'))
                    elements[i].disabled = false;
            }
            stripe.disableStripeInputValidation();
        }
        else
        {
            $(newSubscriptionSection).removeClassName("hide");
            $(existingSubscriptionSection).addClassName("hide");
            stripe.enableStripeInputValidation();
        }
    },

    switchSubscriptionSelected: function(evt)
    {
        var id = 'switch_subscription_date_' + this.value;
        var inputs = $('stripe_payments_subscription_start_date_control').select('input');
        for (var i = 0; i < inputs.length; i++)
        {
            if (inputs[i].id == id)
            {
                $(inputs[i]).removeClassName("hide");
                inputs[i].disabled = false;
            }
            else
            {
                $(inputs[i]).addClassName("hide");
                inputs[i].disabled = true;
            }
        }
    },

    initPaymentFormValidation: function()
    {
        // Adjust validation if necessary
        var hasSavedCards = document.getElementById('new_card');

        if (hasSavedCards)
        {
            var paymentMethods = document.getElementsByName('payment[method]');
            for (var j = 0; j < paymentMethods.length; j++)
                paymentMethods[j].addEventListener("click", stripe.toggleValidation);
        }
    },

    toggleValidation: function(evt)
    {
        $('new_card').removeClassName('validate-one-required-by-name');
        if (evt.target.value == 'stripe_payments')
            $('new_card').addClassName('validate-one-required-by-name');
    },

    initMultiplePaymentMethods: function(selector)
    {
        var wrappers = document.querySelectorAll(selector);
        var countPaymentMethods = wrappers.length;
        if (countPaymentMethods < 2) return;

        var methods = document.querySelectorAll('.indent-target');
        if (methods.length > 0)
        {
            for (var i = 0; i < methods.length; i++)
                this.addClass(methods[i], 'indent');
        }
    },

    placeAdminOrder: function()
    {
        var radioButton = document.getElementById('p_method_stripe_payments');
        if (radioButton && !radioButton.checked)
            return order.submit();

        createStripeToken(function(err)
        {
            if (err)
                alert(err);
            else
                order.submit();
        });
    },

    initAdminStripeJs: function()
    {
        // Stripe.js intercept when placing a new order
        var btn = document.getElementById('order-totals');
        if (btn) btn = btn.getElementsByTagName('button');
        if (btn && btn[0]) btn = btn[0];
        if (btn) btn.onclick = stripe.placeAdminOrder;

        var topBtn = document.getElementById('submit_order_top_button');
        if (topBtn) topBtn.onclick = stripe.placeAdminOrder;
    },

    getSourceOwner: function()
    {
        var owner = {
            name: null,
            email: null,
            phone: null,
            address: {
                city: null,
                country: null,
                line1: null,
                line2: null,
                postal_code: null,
                state: null
            }
        };

        if (stripe.quote)
        {
            var billingAddress = stripe.quote.billingAddress();
            var name = billingAddress.firstname + ' ' + billingAddress.lastname;
            owner.name = name;
            if (stripe.quote.guestEmail)
                owner.email = stripe.quote.guestEmail;
            else
                owner.email = stripe.customer.customerData.email;
            owner.phone = billingAddress.telephone;

            var street = [];

            // Mageplaza OSC delays to set the street because of Google autocomplete,
            // but it does set the postcode correctly, so we temporarily ignore the street
            if (billingAddress.street && billingAddress.street.length > 0)
                street = billingAddress.street;

            owner.address.line1 = (street.length > 0 ? street[0] : null),
            owner.address.line2 = (street.length > 1 ? street[1] : null),
            owner.address.city = billingAddress.city || null,
            owner.address.state = billingAddress.region || null,
            owner.address.postal_code = billingAddress.postcode || null,
            owner.address.country = billingAddress.countryId || null
        }

        if (!owner.phone)
            delete owner.phone;

        return owner;
    },

    // Triggered from the My Saved Cards section
    saveCard: function(saveButton)
    {
        saveButton.disabled = true;

        createStripeToken(function(err)
        {
            if (err)
            {
                alert(err);
                saveButton.disabled = false;
            }
            else
                document.getElementById('payment_form_stripe_payments_payment').submit();
        });

        return false;
    },

    initAdminEvents: function()
    {
        stripe.initRadioButtons();
        stripe.initPaymentFormValidation();
        stripe.initMultiplePaymentMethods('.admin__payment-method-wapper');
    },

    initMultiShippingEvents: function()
    {
        stripe.initRadioButtons();
        stripe.initMultiplePaymentMethods('.methods-payment .item-title');
        stripe.initMultiShippingForm();
    },

    // Multi-shipping form support for Stripe.js
    submitMultiShippingForm: function(e)
    {
        var el = document.getElementById('p_method_stripe_payments');
        if (el && !el.checked)
            return true;

        if (e.preventDefault) e.preventDefault();

        stripe.multiShippingFormSubmitButton = document.getElementById('payment-continue');

        if (stripe.multiShippingFormSubmitButton)
            stripe.multiShippingFormSubmitButton.disabled = true;

        createStripeToken(function(err)
        {
            if (stripe.multiShippingFormSubmitButton)
                stripe.multiShippingFormSubmitButton.disabled = false;

            if (err)
                alert(err);
            else
            {
                if (typeof stripe.multiShippingForm == "undefined" || !stripe.multiShippingForm)
                    stripe.initMultiShippingForm();

                stripe.multiShippingForm.submit();
            }
        });

        return false;
    },

    initMultiShippingForm: function()
    {
        if (stripe.multiShippingFormInitialized) return;

        stripe.multiShippingForm = document.getElementById('multishipping-billing-form');
        if (!stripe.multiShippingForm) return;

        stripe.multiShippingForm.onsubmit = stripe.submitMultiShippingForm;

        stripe.multiShippingFormInitialized = true;
    },

    clearCardErrors: function()
    {
        var box = document.getElementById('stripe-payments-card-errors');

        if (box)
        {
            box.innerHTML = '';
            box.classList.remove('populated');
        }
    },

    validatePaymentForm: function()
    {
        // Dummy method from M1
        return true;
    },

    setLoadWaiting: function(section)
    {
        // Dummy method from M1
    },

    displayCardError: function(message)
    {
        message = stripe.maskError(message);

        // When we use a saved card, display the message as an alert
        var newCardRadio = document.getElementById('new_card');
        if (newCardRadio && !newCardRadio.checked)
        {
            alert(message);
            return;
        }

        var box = document.getElementById('stripe-payments-card-errors');

        if (box)
        {
            box.innerHTML = message;
            box.classList.add('populated');
        }
        else
            alert(message);
    },

    maskError: function(err)
    {
        var errLowercase = err.toLowerCase();
        var pos1 = errLowercase.indexOf("Invalid API key provided".toLowerCase());
        var pos2 = errLowercase.indexOf("No API key provided".toLowerCase());
        if (pos1 === 0 || pos2 === 0)
            return 'Invalid Stripe API key provided.';

        return err;
    },
    shouldSaveCard: function()
    {
        var saveCardInput = document.getElementById('stripe_payments_cc_save');

        if (!saveCardInput)
            return false;

        return saveCardInput.checked;
    },
    getPaymentIntent: function(callback)
    {
        require(['mage/mage', 'mage/url'], function(mage, url) {
            var linkUrl = url.build('rest/V1/stripe/payments/get_payment_intent');
            jQuery.get(linkUrl, {}, function(response)
            {
                try
                {
                    callback(null, response.responseJSON.paymentIntent);
                }
                catch (e)
                {
                    callback("Could not retrieve payment details, please contact us for help");
                    console.error(response);
                }
            });
        });
    },
    handleCardPayment: function(done)
    {
        try
        {
            stripe.closePaysheet('success');

            stripe.stripeJsV3.handleCardPayment(stripe.paymentIntent).then(function(result)
            {
                if (result.error)
                    return done(result.error.message);

                return done();
            });
        }
        catch (e)
        {
            done(e.message);
        }
    },
    handleCardAction: function(done)
    {
        try
        {
            stripe.closePaysheet('success');

            stripe.stripeJsV3.handleCardAction(stripe.paymentIntent).then(function(result)
            {
                if (result.error)
                    return done(result.error.message);

                return done();
            });
        }
        catch (e)
        {
            done(e.message);
        }
    },
    processNextAuthentication: function(done)
    {
        if (stripe.paymentIntents.length > 0)
        {
            stripe.paymentIntent = stripe.paymentIntents.pop();
            stripe.authenticateCustomer(stripe.paymentIntent, function(err)
            {
                if (err)
                    done(err);
                else
                    stripe.processNextAuthentication(done);
            });
        }
        else
        {
            stripe.paymentIntent = null;
            return done();
        }
    },
    authenticateCustomer: function(paymentIntentId, done)
    {
        try
        {
            stripe.stripeJsV3.retrievePaymentIntent(paymentIntentId).then(function(result)
            {
                if (result.error)
                    return done(result.error);

                if (result.paymentIntent.status == "requires_action"
                    || result.paymentIntent.status == "requires_source_action")
                {
                    if (result.paymentIntent.confirmation_method == "manual")
                        return stripe.handleCardAction(done);
                    else
                        return stripe.handleCardPayment(done);
                }

                return done();
            });
        }
        catch (e)
        {
            done(e.message);
        }
    },
    isNextAction3DSecureRedirect: function(result)
    {
        if (!result)
            return false;

        if (typeof result.paymentIntent == 'undefined' || !result.paymentIntent)
            return false;

        if (typeof result.paymentIntent.next_action == 'undefined' || !result.paymentIntent.next_action)
            return false;

        if (typeof result.paymentIntent.next_action.use_stripe_sdk == 'undefined' || !result.paymentIntent.next_action.use_stripe_sdk)
            return false;

        if (typeof result.paymentIntent.next_action.use_stripe_sdk.type == 'undefined' || !result.paymentIntent.next_action.use_stripe_sdk.type)
            return false;

        return (result.paymentIntent.next_action.use_stripe_sdk.type == 'three_d_secure_redirect');
    },
    paymentIntentCanBeConfirmed: function()
    {
        // If stripe.sourceId exists, it means that we are using a saved card source, which is not going to be a 3DS card
        // (because those are hidden from the admin saved cards section)
        return !stripe.sourceId;
    },

    // Converts tokens in the form "src_1E8UX32WmagXEVq4SpUlSuoa:Visa:4242" into src_1E8UX32WmagXEVq4SpUlSuoa
    cleanToken: function(token)
    {
        if (token.indexOf(":") >= 0)
            return token.substring(0, token.indexOf(":"));

        return token;
    },
    closePaysheet: function(withResult)
    {
        try
        {
            if (!stripe.PRAPIEvent)
                return;

            stripe.PRAPIEvent.complete(withResult);
        }
        catch (e)
        {
            // Will get here if we already closed it
        }
    },
    isAuthenticationRequired: function(msg)
    {
        stripe.paymentIntent = null;

        // 500 server side errors
        if (typeof msg == "undefined")
            return false;

        // Case of subscriptions
        if (msg.indexOf("Authentication Required: ") === 0)
        {
            stripe.paymentIntents = msg.substring("Authentication Required: ".length).split(",");
            return true;
        }

        return false;
    },
    createPaymentMethod: function(done)
    {
        var data = {
            billing_details: stripe.getSourceOwner()
        };

        stripe.stripeJsV3.createPaymentMethod('card', stripe.card, data).then(function(result)
        {
            if (result.error)
                return done(result.error.message);

            var cardKey = result.paymentMethod.id;
            var token = result.paymentMethod.id + ':' + result.paymentMethod.card.brand + ':' + result.paymentMethod.card.last4;
            stripeTokens[cardKey] = token;
            setStripeToken(token, result.paymentMethod);

            return done();
        });
    },
    setupCard: function(done)
    {
        if (window.setupIntentClientSecret) // Multishipping checkout & admin
            var clientSecret = window.setupIntentClientSecret;
        else
            var clientSecret = window.stripePaymentForm.getSetupIntentClientSecret();

        if (!clientSecret)
            return done("Could not use SetupIntents to authenticate the card.");

        var options = {
            payment_method: {
                card: stripe.card,
                billing_details: stripe.getSourceOwner()
            }
        };
        stripe.stripeJsV3.confirmCardSetup(clientSecret, options).then(function(result)
        {
            // If there is any type of order placement error, we will need a fresh setupintent for the next request
            if (window.stripePaymentForm)
                window.stripePaymentForm.refreshSetupIntent();
            else
                stripe.refreshSetupIntent();

            if (result.error)
                return done(result.error.message);

            var cardKey = result.setupIntent.payment_method;
            var token = result.setupIntent.payment_method + '::';
            stripeTokens[cardKey] = token;
            setStripeToken(token, result.paymentMethod);

            return done();
        });
    },
    refreshSetupIntent: function()
    {
        var serviceUrl = stripe.urlBuilder.build('/rest/V1/stripe/payments/get_setup_intent', {});
        var self = this;
        window.setupIntentClientSecret = null;

        return stripe.storage.post(
            serviceUrl,
            null,
            false
        )
        .done(function (response)
        {
            window.setupIntentClientSecret = response;
        });
    },
    shouldUseSetupIntents: function()
    {
        if (window.stripePaymentForm)
            return window.stripePaymentForm.useSetupIntents();

        if (window.useSetupIntents == 1)
            return true;
        else
            return false;
    }
};

var createStripeToken = function(callback)
{
    stripe.clearCardErrors();

    if (!stripe.validatePaymentForm())
        return;

    stripe.setLoadWaiting('payment');
    var done = function(err)
    {
        stripe.setLoadWaiting(false);
        return callback(err, stripe.token, stripe.response);
    };

    if (stripe.applePaySuccess)
    {
        return done();
    }

    // First check if the "Use new card" radio is selected, return if not
    var cardDetails, newCardRadio = document.getElementById('new_card');
    if (newCardRadio && !newCardRadio.checked)
    {
        if (stripe.sourceId)
            setStripeToken(stripe.sourceId);
        else
            return done("No card specified");

        return done(); // We are using a saved card token for the payment
    }

    // Check if we are switching from another subscription, return if we are
    var switchSubscription = document.getElementById('switch_subscription');
    if (switchSubscription && switchSubscription.checked) return done();

    try
    {
        if (stripe.shouldUseSetupIntents())
            stripe.setupCard(done);
        else
            stripe.createPaymentMethod(done);
    }
    catch (e)
    {
        return done(e.message);
    }
};

function setStripeToken(token, response)
{
    stripe.token = token;
    if (response)
        stripe.response = response;
    try
    {
        var input, inputs = document.getElementsByClassName('stripe-stripejs--token');
        if (inputs && inputs[0]) input = inputs[0];
        else input = document.createElement("input");
        input.setAttribute("type", "hidden");
        input.setAttribute("name", "payment[cc_stripejs_token]");
        input.setAttribute("class", 'stripe-stripejs--token');
        input.setAttribute("value", token);
        input.disabled = false; // Gets disabled when the user navigates back to shipping method
        var form = document.getElementById('payment_form_stripe_payments_payment');
        if (!form) form = document.getElementById('co-payment-form');
        if (!form) form = document.getElementById('order-billing_method_form');
        if (!form) form = document.getElementById('onestepcheckout-form');
        if (!form && typeof payment != 'undefined') form = document.getElementById(payment.formId);
        if (!form)
        {
            form = document.getElementById('new-card');
            input.setAttribute("name", "newcard[cc_stripejs_token]");
        }
        form.appendChild(input);
    } catch (e) {}
}

function deleteStripeToken()
{
    stripe.token = null;
    stripe.response = null;

    var input, inputs = document.getElementsByClassName('stripe-stripejs--token');
    if (inputs && inputs[0]) input = inputs[0];
    if (input && input.parentNode) input.parentNode.removeChild(input);
}
