define([
    'jquery',
    'Magento_Customer/js/customer-data',
], function ($,customerData) {
    return function(originalWidget) {

        $.widget('mage.sidebar', $.mage.sidebar, {

            _removeItemAfter: function (elem) {
                var productData = customerData.get('cart')().items.filter(function (item) {
                    if(Number(elem.data('cart-item')) === Number(item['item_id'])) {
                        return true;
                    }
                });

                $(document).trigger('ajax:removeFromCart', productData[0]['product_sku']);
            },
        });

        return $.mage.sidebar;
    }
});