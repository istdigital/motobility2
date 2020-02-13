define([
    "jquery"
], function ($, mediaCheck) {
    "use strict";

    $.widget('TemplateMonster.customizationTool', {

        options: {
            blockId: '#customization_tool',
            toggleId: '#ls-icon',
            width: '270px'
        },

        _create: function () {
            var obj = this;
            var customizationBlock = $(this.options.blockId);
            this._setBlockWidth(customizationBlock);

            $(this.options.toggleId).on('click', function (e) {
                var showBlock = !customizationBlock.hasClass('on');
                obj._showBlock(customizationBlock, showBlock);
            });
        },

        _setBlockWidth: function (customizationBlock) {
            var blockWidth = this.options.width;
            customizationBlock.css({
                "width": blockWidth,
                "left": "-" + blockWidth
            });
            customizationBlock.show();
        },

        _showBlock: function (customizationBlock, showBlock) {
            var blockWidth = this.options.width;
            $(this.options.toggleId).toggleClass('fa-close fa-cogs');
            customizationBlock.toggleClass('on').animate({
                left: showBlock ? 0 : "-" + blockWidth
            }, 300);
        }

    });

    return $.TemplateMonster.customizationTool;
});