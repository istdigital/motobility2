define([
    'jquery'
], function ($) {
    'use strict';

    $.widget('zendesk.zendeskSetupGuide', {
        options: {
            url: '',
            elementId: '',
            successText: '',
            failedText: '',
            fieldMapping: ''
        },

        /**
         * Bind handlers to events
         */
        _create: function () {
            this._on({
                'click': $.proxy(this._triggerSetup, this)
            });
        },

        /**
         * Trigger Zendesk account provisioning workflow
         *
         * @private
         */
        _triggerSetup: function () {
            var form = new Element('form', {method: 'post', action: this.options.provisionUrl});


            console.log(this.options);
            console.log(form);

            for (var paramName in this.options.postData) {
                if (!this.options.postData.hasOwnProperty(paramName)) continue;

                form.appendChild(new Element('input', {type: 'hidden', name: paramName, value: this.options.postData[paramName]}));
            }

            document.body.appendChild(form);
            form.submit();
        }
    });

    return $.zendesk.zendeskSetupGuide;
});
