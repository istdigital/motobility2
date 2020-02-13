define(['module', 'Manadev_Core/js/functions/class', 'jquery'],
function(module, class_, $) {
    return class_(module.id, {
        show: function(options) {
            options = $.extend({
                class_name: '',
                duration: 0,
                callback: null
            }, options);

            var overlay = $('<div class="mana-overlay ' + options.class_name + '"></div>');
            overlay.appendTo(document.body);
            overlay.css({left:0, top:0}).width($(document).width()).height($(document).height());

            $('.mana-overlay').fadeIn(options.duration, function () {
                if (options.callback) {
                    options.callback();
                }
            }.bind(this));
        },
        hide: function(options) {
            options = $.extend({
                duration: 0,
                callback: null
            }, options);

            $('.mana-overlay').fadeOut(options.duration, function () {
                $('.mana-overlay').remove();
                if (options.callback) {
                    options.callback();
                }
            }.bind(this));
        }
    });
});