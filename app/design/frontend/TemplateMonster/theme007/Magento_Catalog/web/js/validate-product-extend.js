define([
    'jquery',
    'Magento_Customer/js/customer-data',
], function ($,customerData) {
    return function(originalWidget) {

        $.widget('mage.productValidate', $.mage.productValidate, {

            invalidHandler: function(event, validator) {
                var errors = validator.numberOfInvalids();
                if(errors){



                    // var messages = [];
                    // for (var i = 0; i < validator.errorList.length; i++) {
                    //     if(validator.errorList[i].message.match(/required/)){
                    //         messages.push({
                    //             type: 'error',
                    //             text: $(validator.errorList[i].element).parent().find(".swatch-attribute-label").text() + " is required!"
                    //         })
                    //     }else if($(validator.errorList[i].element).hasClass('qty')){
                    //         messages.push({
                    //             type: 'error',
                    //             text: "Qty is required!"
                    //         })
                    //     }
                    // }

                    customerData.set('messages',{
                        messages: [
                            {
                                type: 'error',
                                text: "Please select all required options."
                            }
                        ],
                        'data_id': Math.floor(Date.now() / 1000)
                    });

                    
                }
            }
        });

        return $.mage.productValidate;
    }
});