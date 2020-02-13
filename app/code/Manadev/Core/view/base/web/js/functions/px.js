define(function() {
    return function (value) {
        value = parseInt(value, 10);
        return isNaN(value) ? 0 : value;
    };
});