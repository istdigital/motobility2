define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'cryozonic_stripe',
                component: 'Cryozonic_StripePayments/js/view/payment/method-renderer/cryozonic_stripe'
            }
        );
        // Add view logic here if needed
        return Component.extend({});
    }
);
