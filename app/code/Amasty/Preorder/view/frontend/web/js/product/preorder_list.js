define([
    'jquery',
    'Amasty_Preorder/js/product/preorder_configurable_list',
    'Magento_Catalog/js/catalog-add-to-cart'
], function($, preOrderConfigurable) {
    'use strict';

    $.widget('mage.amastyPreorderList', {
        options: {
            addToCartButtonSelector: '.tocart',
            parentSelector: '.product-item',
            priceSelector: '.price-box',
            nameSelector: '.product-item-name',
            noteTemplate : '<div class="ampreorder-note" data-preorder-product-id="$productId">$note</div>',
            customStockStatusSelector: '.amstockstatus-category'
        },

        _create: function() {
            this.runPreOrder();
        },

        runPreOrder: function () {
            var self = this;
            $.each(this.options.jsonConfig, function (productId, productConfig) {
                if (productConfig.note || productConfig.cart_label) {
                    var selector = self.getPriceSelector(productId);
                    $(selector).each(function (i, element) {
                        var parent = $(element).parents(self.options.parentSelector);
                        if (parent.length && !parent.hasClass('ampreorder-observed')) {
                            parent.addClass('ampreorder-observed');
                            self.insertPreOrderNote(parent, productId, productConfig);
                            self.changeButton(parent, productConfig);
                        }
                    });
                }

                if (productConfig.configurable && !$.isEmptyObject(productConfig.configurable.map)) {
                    productConfig.configurable.addToCartButton =
                        $(productConfig.configurable.swatchOpt).nextAll('.product-item-inner').find('button');
                    $.mage.amastyPreorderConfigurable(productConfig.configurable);
                }
            });
        },

        getPriceSelector: function (productId) {
            return '[data-product-id="' + productId + '"], '
                + '[id="product-price-' + productId + '"], '
                + '[name="product"][value="' + productId + '"]';
        },

        insertPreOrderNote: function (parent, productId, productConfig) {
            var self = this,
                elementToInsert = $(parent).find(self.options.priceSelector),
                customStockStatus = $(parent).find(self.options.customStockStatusSelector);

            if (!productConfig.note) {
                return;
            }

            if (!elementToInsert.length && !customStockStatus.length) {
                elementToInsert = $(parent).find(self.options.nameSelector);
            }

            var content = self.options.noteTemplate;
            content = content.replace('$productId', productId).replace('$note', productConfig.note);
            if (customStockStatus.length) {
                customStockStatus.replaceWith(content);
            } else if (elementToInsert.length) {
                $(content).insertAfter(elementToInsert.first());
            }
        },

        changeButton: function (parent, productConfig) {
            var self = this,
                button = $(parent).find(self.options.addToCartButtonSelector),
                cartLabel = productConfig.cart_label;

            if (!cartLabel) {
                return;
            }

            button.attr('title', $('<div/>').html(cartLabel).text());
            $.mage.catalogAddToCart.prototype.options.addToCartButtonTextDefault = cartLabel;
            button.html(cartLabel);
        }
    });

    return $.mage.amastyPreorderList;
});
