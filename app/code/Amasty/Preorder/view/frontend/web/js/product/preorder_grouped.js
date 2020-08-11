define([
    'jquery',
    'Amasty_Preorder/js/product/preorder'
], function($) {
    $.widget('mage.amastyPreorderGrouped', $.mage.amastyPreorder, {
        options: {
            map: {},
            preorderNoteTemplate: ''
        },
        _create: function(){
          //super._create();
            this._saveOriginal();
            this._changeLabels();
            var self = this;
            $.each(this.options.map, function(key, value){
                $('.qty input[name=super_group\\['+key+'\\]]').change(function(){
                   if(this.value > 0) {
                       self.options.addToCartLabel = value.cartLabel;
                       self.options.preOrderNote = value.note;
                       self.enable();
                   } else {
                       self.disable();
                   }
                });
                $('.grouped .price-box.price-final_price[data-product-id='+key+']').append(self.options.preorderNoteTemplate.replace('{preorderNote}', value.note));

            });
        },

        _changeLabels: function() {
            if (this.options.preorder && this.options.preOrderNote) {
                this._changeAvailability();
            }
        }
    });
});
