/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */


define([
    'jquery',
    'TemplateMonster_FilmSlider/js/slider/item/sliderpagedit',
    'MutationObserver'
],function($){
'use strict';

    /**
     * Observe canvas on insert or delete
     * item trigger events
     */
    $.widget('tm.sliderItemLayerImage', $.tm.sliderPageEdit, {
        _create: function() {

            var canvasId = '#'+this.element.attr('id');
            var target = document.querySelector(canvasId);
            var MutationObserver = window.MutationObserver;

            var observer = new MutationObserver($.proxy(function(mutations) {
                mutations.forEach($.proxy(function(mutation) {

                    for (var i = 0; i < mutation.addedNodes.length; i++) {
                        this._layerItemDraggable(mutation.addedNodes[i]);
                    }
                    this._addNodesEventsManager(mutation.addedNodes.length);

                    for (var i = 0; i < mutation.removedNodes.length; i++) {
                        this._removeNodeFormCanvas(mutation.removedNodes[i]);
                    }
                    this._removeNodesEventsManager(mutation.removedNodes.length);

                },this));

            },this));
            var config = { attributes: true, childList: true, characterData: true };
            observer.observe(target, config);

            $(this.element).on("click", "div", $.proxy(function(event){
                this._selectLayerItem(event);
                var elementId = $(event.target).data('id');
                $(this.element).trigger(this.options.eventLayerItemClicked,[elementId]);
            },this));

        },

        /**
         * Made all insert item draggable
         * @param node
         * @private
         */
        _layerItemDraggable : function(node)
        {
            $(node).draggable();
        },

        _removeNodeFormCanvas: function(node){
            var itemId = $(node).data('id');
            if(itemId) {
                this._removeLayoutDataById(itemId);
                this._removeImageSortableItemById(itemId);
            }
        },

        _addNodesEventsManager: function(length) {
            if(length == 1) {
                $(this.element).trigger(this.options.eventLayerItemAdded);
            } else if(length > 1) {
                $(this.element).trigger(this.options.eventLayerItemManyAdded);
            }
        },

        _removeNodesEventsManager: function(length) {
            if(length == 1) {
                $(this.element).trigger(this.options.eventLayerItemRemoved);
            } else if(length > 1) {
                $(this.element).trigger(this.options.eventLayerItemManyRemoved);
            }
        },

    });

    return $.tm.sliderItemLayerImage;
});