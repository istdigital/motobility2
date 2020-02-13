define(['jquery', 'mage/loader'], function($) {
    $.widget('Manadev_Core.widget', {
        options: {},
        events: {},
        
        _create: function() {
            this.attachEvents('events', this.element);
        },
        _destroy: function() {
            this.detachEvents('events', this.element);
        },

        attachEvents: function (events, $el) {
            this.attachOrDetachEvents(events, $el, 'on');
        },
        detachEvents: function(events, $el) {
            this.attachOrDetachEvents(events, $el, 'off');
        },
        attachOrDetachEvents: function(events, $el, onOrOff) {
            if (!this[events + '_handlerProxies']) {
                this.createEventHandlerProxies(events);
            }

            var handlers = this[events + '_handlerProxies'];

            $.each(this[events], function (event) {
                var pos = event.indexOf(' ');

                if (pos !== -1) {
                    $el[onOrOff](event.substr(0, pos), event.substr(pos + 1), handlers[event]);
                }
                else {
                    $el[onOrOff](event, handlers[event]);
                }
            });

        },
        createEventHandlerProxies: function (events) {
            var self = this;
            self[events + '_handlerProxies'] = {};

            $.each(this[events], function(event, handler) {
                self[events + '_handlerProxies'][event] = $.proxy(self[handler], self);
            });
        }
    });

    return $.Manadev_Core.widget;
});