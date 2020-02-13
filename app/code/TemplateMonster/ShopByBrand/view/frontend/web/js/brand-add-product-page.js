define([
    'jquery',
    'underscore',
    'mage/template',
    'text!TemplateMonster_ShopByBrand/templates/brand/image.html'
],function($, _, mageTemplate, brandTemplate) {
    'use strict';

    $.widget('tm.brandAddProductPage', {
        options: {
            template: brandTemplate,
            showLogo: true,
            showName: true,
            logoWidth: 200,
            selector: '',
            brand: {
                name: null,
                image: null,
                url: null
            }
        },

        _create: function() {
            if (this._isNeedToShow) {
                $(this.options.selector).after(this._renderBrandLogo());
            }
        },

        _isNeedToShow: function() {
            var o = this.options;

            // check if all options is initialized
            return _.any([o.showLogo, o.showName]) && _.all(o.brand);
        },

        _renderBrandLogo: function() {
            var template = mageTemplate(this.options.template);
            var o = this.options;

            return template({
                data: {
                    showLogo: o.showLogo,
                    showName: o.showName,
                    logoWidth: o.logoWidth,
                    brand: o.brand
                }
            });
        }
    });

    return $.tm.brandAddProductPage;
});