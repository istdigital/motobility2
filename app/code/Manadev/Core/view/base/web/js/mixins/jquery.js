define(function () {
    return function ($) {
        var origShow = $.fn.show;

        $.fn.show = function () {
            var r = origShow.apply(this, arguments);
            $(document).trigger('mana-after-show', [this]);
            return r;
        };

        return $;
    };
});