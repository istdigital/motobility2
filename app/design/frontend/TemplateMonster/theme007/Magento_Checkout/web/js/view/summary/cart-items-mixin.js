define([], function () {
    'use strict';

    var mixin = {
        isItemsBlockExpanded: function () {
            return true;
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});