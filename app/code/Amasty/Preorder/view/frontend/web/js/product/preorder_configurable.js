define([
    'jquery',
    'Amasty_Preorder/js/product/preorder'
], function($) {
    $.widget('mage.amastyPreorderConfigurable', $.mage.amastyPreorder, {
        options: {
            map: [],
            currentAttributes: {},
            isAllProductsPreorder: 0
        },

        _create: function(){
            this._saveOriginal();

            if(this.options.isAllProductsPreorder == 1) {
                this.enable();
            }

            //super._create();
            var self = this;

            $('.swatch-opt').click(function(){
                self.update();
            });

            $('.super-attribute-select').change(function(){
                self.update();
            });

        },

        update: function (event) {
            var attributeValue;
            var isChanged = false;
            for(var attributeId in this.options.currentAttributes) {
                attributeValue = this.options.currentAttributes[attributeId];
                var $element = $('[data-attribute-id=' + attributeId + '], [attribute-id=' + attributeId + ']');
                var selected;

                if($element.length) {
                    selected = $element.attr('option-selected') || $element.attr('data-option-selected');
                } else if($('#attribute' + attributeId).length) {
                    selected = $('#attribute' + attributeId).first().val();
                }

                if(selected != attributeValue) {
                    isChanged = true;
                    this.options.currentAttributes[attributeId] = selected;
                }
            }

            if(isChanged) {
                this.onChangeProductAttributes();
            }
        },

        onChangeProductAttributes: function(){
            var currentProductId = false;
            var self = this;

            for (var productId in this.options.map) {
                if (!this.options.map.hasOwnProperty(productId)) {
                    break;
                }

                var productInfo = this.options.map[productId];

                currentProductId = productId;
                $.each(productInfo.attributes, function( attributeId, attributeValue ) {
                    if(self.options.currentAttributes[attributeId] != attributeValue) {
                        currentProductId = false;
                    }
                });

                if(currentProductId) {
                    break;
                }
            }

            if(this.options.map[currentProductId] || this.options.isAllProductsPreorder == 1) {
                if (this.options.map[currentProductId]) {
                    this.options.addToCartLabel = this.options.map[currentProductId]['cartLabel'];
                    this.options.preOrderNote = this.options.map[currentProductId]['note'];
                }
                this.enable();
            } else {
                this.disable();
            }
        }
    });
});
