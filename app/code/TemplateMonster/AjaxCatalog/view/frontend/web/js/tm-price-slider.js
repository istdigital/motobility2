define([
    'jquery',
    'jquery/ui'
], function($){
    $.widget('tm.priceRangeSlider', {
        options:{
            minPrice: null,
            maxPrice: null,
            minField: '#minPrice',
            maxField: '#maxPrice',
            range: null,
            url: null,
            paramName: 'price',
            defaultValue: null,
            submitSelector: '#slider-range_submit',
            precision: .01
        },

        _create: function() {
            var self = this,
                options = self.options,
                minField = options.minField,
                maxField = options.maxField,
                minPrice = parseFloat(options.minPrice),
                maxPrice = parseFloat(options.maxPrice);

            // var slider = this.element.slider({
            //     range: true,
            //     min: minPrice,
            //     max: maxPrice,
            //     step: options.precision,
            //     values: [ minPrice, maxPrice ],
            //     slide: function( event, ui ) {
            //         var resultMin = parseFloat(ui.values[ 0 ]).toFixed(2),
            //             resultMax = parseFloat(ui.values[ 1 ]).toFixed(2);

            //         $(minField).val(resultMin);
            //         $(maxField).val(resultMax);

            //         options.range = resultMin + '-' + resultMax;

            //         self._updateUrl();
            //     }
            // });

            $(minField).on('change', function(){
                //slider.slider("values", [$(this).val(), $(minField).val()]);

                options.range = $(this).val() + '-' + $(maxField).val();
                self._updateUrl();
            });

            $(maxField).on('change', function(){
                //slider.slider("values", [$(minField).val(), $(this).val()]);

                options.range = $(minField).val() + '-' + $(this).val();
                self._updateUrl();
            });
        },

        _updateUrl: function(){
            var self = this,
                options = self.options,
                submitButton = $(options.submitSelector);

            submitButton.attr(
                'href',
                self._changeUrl(options.paramName, options.range, options.defaultValue )
            );
        },

        _changeUrl: function(paramName, paramValue, defaultValue){
            var urlPaths = this.options.url.split('?'),
                baseUrl = urlPaths[0],
                urlParams = urlPaths[1] ? urlPaths[1].split('&') : [],
                paramData = {},
                parameters;
            for (var i = 0; i < urlParams.length; i++) {
                parameters = urlParams[i].split('=');
                paramData[parameters[0]] = parameters[1] !== undefined
                    ? window.decodeURIComponent(parameters[1].replace(/\+/g, '%20'))
                    : '';
            }
            paramData[paramName] = paramValue;
            if (paramValue == defaultValue) {
                delete paramData[paramName];
            }

            paramData = $.param(paramData);

            return baseUrl + (paramData.length ? '?' + paramData : '');
        }
    });

    return $.tm.priceRangeSlider;
});