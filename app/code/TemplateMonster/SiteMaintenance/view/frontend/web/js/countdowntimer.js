define([
    "jquery",
    "jquery.countdown.sm"
], function($) {
    "use strict";
    $.widget('tm.smcountdowntimer', {
        _create: function() {
            var format = this.options.format;
            this.element.countdown(this.options.end_date, function(event) {
                $(this).html(
                    event.strftime(format)
                );
            });
        }
    });
    return $.tm.smcountdowntimer;
});