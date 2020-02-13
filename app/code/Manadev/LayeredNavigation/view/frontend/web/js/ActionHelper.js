define(['module', 'Manadev_Core/js/functions/class', 'jquery'], function(module, class_, $) {
    return class_(module.id, {
        parse: function(action) {
            if (!action) {
                return null;
            }

            var match = action.match(/([+-]?)(.*)=(.*)/);
            if (!match) {
                return null;
            }

            return {op: match[1], param: match[2], value: match[3]};
        },
        forEachElement: function(selector, action, callback) {
            var actions = this.splitActions(action);

            $(selector).each(function(index, element) {
                var action = this.getElementAction(element);
                if (!action) {
                    return;
                }

                if (!actions[action.param]) {
                    return;
                }

                if (!action.value) {
                    callback.call(element, actions[action.param]);
                    return;
                }

                if (action.op && actions[action.param].value && action.value != actions[action.param].value) {
                    return;
                }

                callback.call(element, {
                    param: action.param,
                    value: action.value,
                    op: actions[action.param].op,
                    clear: !actions[action.param].value || action.value != actions[action.param].value});
            }.bind(this));
        },
        splitActions: function(action) {
            var result = {};
            if (!action) {
                return result;
            }

            action.split('&').forEach(function (action) {
                if (!(action = this.parse(action))) {
                    return;
                }

                result[action.param] = action;
            }.bind(this));

            return result;
        },
        getElementAction: function(element) {
            var actions = this.splitActions($(element).data('action'));
            if (actions.p) {
                delete actions.p;
            }
            var keys = Object.keys(actions);
            if (keys.length > 1) {
                return '';
            }
            else {
                return actions[keys[0]];
            }
        }
    });
});