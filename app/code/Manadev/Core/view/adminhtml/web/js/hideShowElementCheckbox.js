define([
    'jquery',
    'mage/loader'
], function ($) {
    $.widget("mana.hideShowElementCheckbox", {
        options: {
            affectedElements: [],
            afterChange: false
        },

        _create: function() {
            $(this.element)
                .on('change', $.proxy(this.showOrHide, this));
            this.showOrHide();
        },
        isVisible: function () {
            return $(this.element.context).val() == "1";
        },
        showOrHide: function () {
            var arr = this.options.affectedElements;
            for(var i = 0; i < arr.length; i++ ) {
                var affectedElement = $(arr[i]);
                if (this.isVisible()) {
                    affectedElement.show();
                } else {
                    affectedElement.hide();
                }
            }
            if(typeof this.options.afterChange == "function") {
                this.options.afterChange.apply();
            }
        }
    });

    return $.mana.hideShowElementCheckbox;
});