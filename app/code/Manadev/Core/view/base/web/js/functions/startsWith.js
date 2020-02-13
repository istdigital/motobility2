define(function() {
    return function(haystack, needle) {
        return haystack.indexOf(needle) === 0;
    };
});