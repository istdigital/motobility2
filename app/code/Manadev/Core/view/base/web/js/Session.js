define(['module', 'Manadev_Core/js/functions/class', 'Manadev_Core/js/Data', 'jquery'],
function(module, class_, Data, $) {
    return class_(module.id, Data, {
        __constructor: function (data) {
            this.values = {};
            Data.prototype.__constructor.call(this, data);
        },
        save: function(key, value) {
            this.values[key] = value;
            $.post(this.url, {key: key, value: JSON.stringify(value)});
        },
        restore: function (key) {
            return this.values[key];
        }
    });
});