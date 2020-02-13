define(['module', 'Manadev_Core/js/functions/class'], function(module, class_) {
    return class_(module.id, {
        /**
         * @param {Object=} data
         */
        __constructor: function(data) {
            this.set(data);
        },

        set: function(data) {
            data = data || {};
            for (var property in data) {
                if (!data.hasOwnProperty(property)) continue;

                this[property] = data[property];
            }
        }
    });
});