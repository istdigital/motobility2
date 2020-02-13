define([
    'jquery',
    'underscore',
    'text!TemplateMonster_NewsletterPopup/templates/modal-popup.html',
    'jquery/validate',
    'jquery/jquery.cookie',
    'Magento_Ui/js/modal/modal',
    'mage/loader'
], function ($, _, popupTpl) {
    'use strict';

    $.widget('tm.newsletterPopup', {
        options: {
            title: '',
            content: '',
            submit: '',
            cancel: '',
            cookieName: 'tm-newsletter-popup',
            footerLinkSelector: '.newsletter-popup-link',
            customClass: '',
            isShowSpinner: true,
            timeout: 0,
            isShowOnStartup: true,
            isShowOnFooter: true,
            popupTpl: popupTpl,
            socialLinks: []
        },
        STATE_SUBSCRIBED: 2,
        STATE_STARTUP_SHOWN: 4,
        STATE_FOOTER_SHOWN: 8,
        _currentState: null,
        _modal: null,

        _create: function() {
            this._bind();
            this._modal = this._createModal();
            setTimeout(this._showOnStartup, this.options.timeout * 1000);
        },

        _bind: function() {
            _.bindAll(this, '_windowScrollHandler', '_linkClickHandler', '_formSubmitHandler', '_showOnStartup', '_showModal', '_closeModal', '_onClose', '_subscribe', '_cancel', '_successHandler');
            $('form', this.element).submit(this._formSubmitHandler);
            $(this.options.footerLinkSelector).click(this._linkClickHandler);
            if (this.options.isShowOnFooter) {
                $(window).scroll(this._windowScrollHandler);
            }
        },

        _createModal: function () {
            var element, submit, cancel;

            var inputCheckPrivacy = $('.check_privacy');

            element = $(this.element);
            submit = element.find('button.subscribe');
            cancel = element.find('button.cancel');
            if($(".privacy-policy-check-box").length) {
                $(submit).prop("disabled", true).addClass('disable-checked');
                $(inputCheckPrivacy).change(function () {
                    if (this.checked) {
                        $(submit).prop("disabled", false).removeClass('disable-checked');
                    } else {
                        $(submit).prop("disabled", true).addClass('disable-checked');
                    }
                });
            }

            submit.on('click', this._subscribe);
            cancel.on('click', this._cancel);

            return $(this.element).modal({
                title: this.options.title,
                content: this.options.content,
                buttons: [
                //     {
                //         text: this.options.submit,
                //         class: 'subscribe',
                //         click: this._subscribe
                //     },
                //     {
                //         text: this.options.cancel,
                //         click: this._cancel
                //     }
                ],
                closed: this._onClose,
                modalClass: 'newsletter-popup ' + this.options.customClass,
                popupTpl: popupTpl,
                socialLinks: this.options.socialLinks,
                clickableOverlay: true
            });
        },

        _windowScrollHandler: function() {
            if (!this._isScrolledToFooter()) {
                return;
            }

            if (!this._isHasState(this.STATE_FOOTER_SHOWN, this.STATE_SUBSCRIBED)) {
                this._currentState = this.STATE_FOOTER_SHOWN;
                this._showModal();
            }
        },

        _linkClickHandler: function(event) {
            this._showModal();
            event.preventDefault();
        },

        _formSubmitHandler: function(event) {
            var form = $(event.currentTarget);

            if (!form.validation('isValid')) {
                return;
            }

            var url = form.attr('action'),
                params = form.serialize();

            $.ajax(url, {
                method: 'POST',
                data: params,
                showLoader: this.options.isShowSpinner
            }).done(this._successHandler).fail(this._closeModal);

            event.preventDefault();
        },

        _showOnStartup: function() {
            if (!this.options.isShowOnStartup) {
                return;
            }

            if (!this._isHasState(this.STATE_STARTUP_SHOWN, this.STATE_SUBSCRIBED)) {
                this._currentState = this.STATE_STARTUP_SHOWN;
                this._showModal();
            }
        },

        _showModal: function() {
            this._modal.trigger('openModal');
        },

        _closeModal: function() {
            this._modal.trigger('closeModal');
        },

        _onClose: function() {
            this._updateState(this._currentState);
        },

        _subscribe: function() {
            this.element.find('form').submit();
        },

        _cancel: function() {
            this._updateState(this._currentState);
            this._closeModal();
        },

        _isScrolledToFooter: function() {
            var page = $(window),
                footer = $('footer');

            var pageTop = page.scrollTop(),
                pageBottom = pageTop + page.height();

            var footerTop = footer.offset().top,
                footerBottom = footerTop + footer.height();

            return footerTop >= pageTop && footerBottom <= pageBottom;
        },

        _successHandler: function() {
            this._updateState(this.STATE_SUBSCRIBED);
            this._closeModal();
        },

        _updateState: function(state) {
            if (!state) {
                return;
            }

            var currentState = Number($.cookie(this.options.cookieName));
            var newState = currentState | state;

            var options = {};
            if (newState & this.STATE_SUBSCRIBED) {
                options.expires = 365;
            }

            $.cookie(this.options.cookieName, newState, options);
        },

        _isHasState: function() {
            var currentState = Number($.cookie(this.options.cookieName));

            return _.any(_.map(arguments, function(state) {
                return (currentState & state) == state;
            }))
        }
    });

    return $.tm.newsletterPopup;
});