define([
    "jquery",
    "jquery.countdown"
], function($) {
    "use strict";
    $.widget('tm.countdowntimer', {
        _create: function() {
            this._move();
            var format = this.options.format;
            this.element.countdown(this.options.end_date, function(event) {
                $(this).html(
                    event.strftime(format)
                );
            });
        },
        _move:function() {
            var closestClass = '.product-item-info';
            switch (this.options.area) {
                case "0":
                    closestClass = '.product-info-main';
                    break;
                case "1":
                    closestClass = '.product-item-info';
                    break;
                case "2":
                    closestClass = '.product-item-info';
                    break;
            }
            var selector = this.options.selector;
            var timerWrapper = this.element.closest('.timer-wrapper');
            var closesProductWrapper = this.element.closest(closestClass);
            var matchElement = closesProductWrapper.find(selector);
            if (matchElement.length != 0) {
                timerWrapper.detach();
                timerWrapper.insertAfter(matchElement);
            }
        }
    });
    return $.tm.countdowntimer;
});