define([
    "jquery",
    "jquery/ui",
    "sliderInit",
], function ($, sliderPro) {
    "use strict";

    $.widget('TemplateMonster.FilmSlider', {

        options: {
            width: '100%',
            height: 500
        },

        _create: function () {
            var sliderContainer = this.element,
                sliderObject = this,
                proContainer = $('.slider-pro-container'),
                loaderMask = $('.slider-pro-container .loading-mask.for-slider');

            //Layers on first slide
            var firstAnimatingElems = sliderContainer.find('.sp-slide:first').find("[data-animation ^= 'animated']");

            //Initialize carousel
            var params = this._optionsCustomize(this.options);
            var slider = sliderContainer.sliderPro(params).hide().fadeIn(100, function () {
                proContainer.css('height', 'auto');
            });
            loaderMask.fadeOut(200);

            //Animate captions in first slide on page load
            this._doAnimations(firstAnimatingElems);

            //Other slides to be animated on carousel slide event
            this._getCurrentLayers(sliderObject, sliderContainer, slider);

            sliderContainer.on('gotoSlide sliderResize', function () {
                sliderObject._customOffsetLayer(sliderObject, sliderContainer);
            }).trigger('gotoSlide');

        },

        _customOffsetLayer: function (sliderObject, sliderContainer) {
            $('.sp-layer', sliderContainer).each(function () {
                if ($(this).data('position') == 'topCenter' || $(this).data('position') == 'bottomCenter' || $(this).data('position') == 'centerCenter') {
                    sliderObject._makeOffset(sliderObject, $(this));
                } else {
                    return;
                }
            })
        },

        _makeOffset: function (sliderContainer, layer) {
            var windowWidth = $(window).width();
            var layerWidth = parseInt(layer.css('width'));
            // var originOffset = parseInt(layer.css('left'));
            if (layerWidth >= windowWidth) {
                layer.css('left', -1 * (layerWidth - windowWidth) / 2);
            }
        },

        _doAnimations: function (elems) {

            //Cache the animationend event in a variable
            var animEndEv = 'webkitAnimationEnd animationend';

            elems.each(function () {
                var $this = $(this),
                    $animationType = $this.data('animation'),
                    $delay = $this.data('show-delay');

                // Animation after delay
                setTimeout(function () {
                    $this.addClass($animationType).one(animEndEv, function () {
                        $this.removeClass($animationType);
                    });
                }, $delay);
            });
        },

        _getCurrentLayers: function (sliderObject, sliderContainer, slider) {
            slider.on('gotoSlide', function (event) {
                var animatingElems = $('.sp-slide.sp-selected', sliderContainer).find("[data-animation ^= 'animated']");
                sliderObject._doAnimations(animatingElems);
            });
        },

        _optionsCustomize: function (options) {

            var params = JSON.parse(JSON.stringify(options), function (name, value) {

                value = (value === "true") ? true : value;
                value = (value === "false") ? false : value;

                if (!isNaN(parseInt(value))) {
                    if (( name == 'width' || name == 'height') && value[value.length - 1] == '%') {
                        return value;
                    } else {
                        value = Number(value);
                    }
                }
                return value;
            });
            return params;
        }

    });

    return $.TemplateMonster.FilmSlider;

});