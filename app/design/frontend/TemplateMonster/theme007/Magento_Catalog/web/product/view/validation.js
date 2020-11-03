/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

(function (factory) {
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define([
            'jquery',
            'Magento_Customer/js/customer-data',
            'jquery/ui',
            'mage/validation/validation'
        ], factory);
    } else {
        factory(jQuery);
    }
}(function ($, customerData) {
    'use strict';

    $.widget('mage.validation', $.mage.validation, {
        options: {
            radioCheckboxClosest: 'ul, ol',

            /**
             * @param {*} error
             * @param {HTMLElement} element
             */
            errorPlacement: function (error, element) {
                var messageBox,
                    dataValidate;

                if ($(element).hasClass('datetime-picker')) {
                    element = $(element).parent();

                    if (element.parent().find('[generated=true].mage-error').length) {
                        return;
                    }
                }

                if (element.attr('data-errors-message-box')) {
                    messageBox = $(element.attr('data-errors-message-box'));
                    messageBox.html(error);

                    return;
                }

                dataValidate = element.attr('data-validate');

                if (dataValidate && dataValidate.indexOf('validate-one-checkbox-required-by-name') > 0) {
                    error.appendTo('#links-advice-container');
                } else if (element.is(':radio, :checkbox')) {
                    element.closest(this.radioCheckboxClosest).after(error);
                } else {
                    element.after(error);
                }
            },

            /**
             * @param {HTMLElement} element
             * @param {String} errorClass
             */
            highlight: function (element, errorClass) {
                var dataValidate = $(element).attr('data-validate');

                if (dataValidate && dataValidate.indexOf('validate-required-datetime') > 0) {
                    $(element).parent().find('.datetime-picker').each(function () {
                        $(this).removeClass(errorClass);

                        if ($(this).val().length === 0) {
                            $(this).addClass(errorClass);
                        }
                    });
                } else if ($(element).is(':radio, :checkbox')) {
                    $(element).closest(this.radioCheckboxClosest).addClass(errorClass);
                } else {
                    $(element).addClass(errorClass);
                }
            },

            /**
             * @param {HTMLElement} element
             * @param {String} errorClass
             */
            unhighlight: function (element, errorClass) {
                var dataValidate = $(element).attr('data-validate');

                if (dataValidate && dataValidate.indexOf('validate-required-datetime') > 0) {
                    $(element).parent().find('.datetime-picker').removeClass(errorClass);
                } else if ($(element).is(':radio, :checkbox')) {
                    $(element).closest(this.radioCheckboxClosest).removeClass(errorClass);
                } else {
                    $(element).removeClass(errorClass);
                }
            },

            invalidHandler: function(event, validator) {

                var errors = validator.numberOfInvalids();
                if(errors){
                    customerData.set('messages',{
                        messages: [
                            {
                                type: 'error',
                                text: "Please select all required options."
                            }
                        ],
                        'data_id': Math.floor(Date.now() / 1000)
                    });

                    setTimeout(function(){
                        customerData.set('messages',{});
                    }, 3000)
                }
            }
        }
    });

    return $.mage.validation;
}));
