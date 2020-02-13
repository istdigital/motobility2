define([
    'jquery',
    'Manadev_Core/js/hideShowElementCheckbox',
    'mage/loader'
], function ($) {
    $.widget("mana.hideShowElementSelect", $.mana.hideShowElementCheckbox, {
        options: {
            show: true,
            ifValueIn: [],
            affectedElements: [],
            invertAffectedElements: [],
            afterChange: false
        },
        isInValueIn: function () {
            var val = $(this.element.context).val();

            // $.inArray returns the index of value, not boolean.
            return $.inArray(val, this.options.ifValueIn) !== -1;
        },
        showOrHide: function () {
            var self = this;
            function _loopEachElement(arr) {
                for(var i = 0; i < arr.length; i++ ) {
                    var affectedElement = $(arr[i]);
                    if (self.isInValueIn()) {
                        if(self.options.show) {
                            affectedElement.show();
                        } else {
                            affectedElement.hide();
                        }
                    } else {
                        if (self.options.show) {
                            affectedElement.hide();
                        } else {
                            affectedElement.show();
                        }
                    }
                }
            }

            _loopEachElement(this.options.affectedElements);
            this.options.show = !this.options.show;
            _loopEachElement(this.options.invertAffectedElements);
            this.options.show = !this.options.show;

            if(typeof this.options.afterChange == "function") {
                this.options.afterChange.apply();
            }
        }
    });

    return $.mana.hideShowElementSelect;
});