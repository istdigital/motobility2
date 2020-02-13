define(['jquery', 'mage/collapsible', 'Manadev_Core/js/vars/session',
    'Manadev_Core/js/functions/requestAnimationFrame', 'mage/loader'],
function($, collapsible, session, requestAnimationFrame) {
    var state = {};

    $.widget('Manadev_Core.StatefulCollapsible', collapsible, {
        _getState: function() {
            return {
                active: this.header.attr('aria-expanded') === 'true'
            };
        },

        _setState: function(state) {
            this.options.active = state.active;
            if (state.active) {
                this.content.show();
                this.header.attr('aria-expanded', 'true');
            }
            else {
                this.content.hide();
                this.header.attr('aria-expanded', 'false');
            }
        },

        _saveStateInSession: function() {
            session.save(this.options.id, this._getState());
        },

        _restoreStateFromSession: function() {
            var state;

            try {
                state = session.restore(this.options.id);
                if (state) {
                    this._setState(state);
                }
            }
            catch (e) {
            }
        },

        _saveStateInMemory: function() {
            state[this.options.id] = this._getState();
        },

        _restoreStateFromMemory: function() {
            if (state[this.options.id]) {
                this._setState(state[this.options.id]);
                delete state[this.options.id];
            }
        },

        _processState: function() {
            $(document).on('mana-before-replacing-content',
                this._bound_beforeReplacingContent = this._beforeReplacingContent.bind(this));
            this.element.on('dimensionsChanged',
                this._bound_onDimensionsChanged = this._onDimensionsChanged.bind(this));

            if ($(document).data('mana-replacing-content')) {
                this._restoreStateFromMemory();
            }
            else {
                this._restoreStateFromSession();
            }

            return this._super();
        },

        _beforeReplacingContent: function(event, $containers) {
            if ($containers.has(this.element[0]).length) {
                this._saveStateInMemory();
            }
        },

        _onDimensionsChanged: function() {
            if (this._dimensionsChanged) {
                return;
            }

            this._dimensionsChanged = true;

            requestAnimationFrame(this._afterAllDimensionsChanged.bind(this));

        },

        _afterAllDimensionsChanged: function() {
            this._dimensionsChanged = false;
            this._saveStateInSession();
        },

        _destroy: function() {
            this._super();
            $(document).off('mana-before-replacing-content', this._bound_beforeReplacingContent);
            this.element.off('dimensionsChanged', this._bound_onDimensionsChanged);
        }
    });

    return $.Manadev_Core.StatefulCollapsible;
});