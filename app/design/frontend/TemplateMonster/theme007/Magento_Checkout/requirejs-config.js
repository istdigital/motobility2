/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    //map: {
    //    '*': {
    //        "miniCartExtending":     "Magento_Checkout/js/miniCartExtendingFile"
    //    }
    //}
    config: {
        mixins: {
            'Magento_Checkout/js/view/minicart': {
                'Magento_Checkout/js/view/minicart-extend': true
            },
            "Magento_Checkout/js/sidebar": {
                'Magento_Checkout/js/sidebar-mixin': true
            }
        }
    }
};
