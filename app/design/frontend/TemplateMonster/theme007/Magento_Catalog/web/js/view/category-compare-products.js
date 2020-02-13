/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'jquery',
    'underscore',
    'mage/mage',
], function (Component, customerData, $, _) {
    'use strict';

    return Component.extend({
        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.compareProducts = customerData.get('compare-products');
            this.compareProducts.subscribe(function(k){
                $("a.product-items-compare-link").each(function(i,v){
                    var id = $(this).data('id');
                    var r = _.findWhere(k.items, {id: id.toString()});
                    if(r){
                        $(this).attr('data-post',r.remove_url);
                        $(this).find('i').removeClass("icon-check-empty").addClass("icon-ok-squared");
                    }
                });
            });
        }
    });
});
