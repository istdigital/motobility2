/*jshint browser:true jquery:true*/
/*global alert*/
var config = {
    map: {
        '*': {
            'cryozonic_stripe': 'Cryozonic_StripePayments/js/cryozonic_stripe'
        }
    },
    config: {
        mixins: {
            'Magento_Ui/js/view/messages': {
                'Cryozonic_StripePayments/js/messages-mixin': true
            },
            'MSP_ReCaptcha/js/ui-messages-mixin': {
                'Cryozonic_StripePayments/js/messages-mixin': true
            }
        }
    }
};
