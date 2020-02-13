define([
    'jquery',
    'mage/mage'
], function ($, mage) {
    'use strict';

    /**
     * Rating start click/hover interactions
     * @param element Form object
     * @param star  Star object
     */
    function ratingStarsInteraction(element){
        var fillClass   = 'fill';
        var star        = '.review-control-vote label[class^="rating-"]';

        $(element).on('click', star, function(){
            operateClasses(fillClass, $(this));
        });

        //$(element).on('mouseenter', star, function(){
        //});
        //
        //$(element).on('mouseleave', star, function(){
        //});
    }

    /**
     * Adds/removes stars classes
     * @param fill Class Fill class
     * @param id Data Id of the star element
     */
    function operateClasses(fillClass, item){
        var data_id     = getDataId(item);
        var data_name   = getDataName(item);
        var i;

        for( i = 1; i <= 5; i++){
            var label = $('label[data-id="' + i + '"][data-name="' + data_name + '"]');

            $.when( label.removeClass(fillClass) ).done(function(){
                if (i <= data_id){
                    label.addClass(fillClass);
                }
            })
        }
    }

    /**
     * Get id of the star element
     * @param selector Star element
     * @returns {Number} Star data-id
     */
    function getDataId(selector){
        return parseInt(selector.attr('data-id'));
    }

    function getDataName(selector){
        return selector.attr('data-name');
    }

    return function (config, element) {
        ratingStarsInteraction(element);
    };
});
