define([
    'jquery',
    'underscore'
], function ($, _) {
    'use strict';

    $.widget('TemplateMonster.switchImage', {

        options: {
            duration: 300,
        },

        _create: function() {
            this._replaceImage();
            this._playVideo();
        },

        _replaceImage: function() {
            var self = this.element;
            var duration = this.options.duration;
            $(self).hover(
                function() {
                    $('.base', this).fadeTo(duration, 0);
                    $('.replaced', this).stop(true,false).fadeIn(duration);
            },
                function () {
                    $('.replaced', this).fadeOut(duration);
                    $('.base', this).stop(true,false).fadeTo(duration, 1);
                }
            );
        },

        _playVideo: function() {
            if(!$(this.element).hasClass('is-video')) return false;

            var self = this.element;
            var duration = this.options.duration;

            $(self).hover(
                function() {
                    $('.base', this).fadeTo(duration, 0);
                    $('.switch-video', this).fadeIn(duration);

                    var videoURL = $('iframe', self).prop('src');
                    videoURL += "&autoplay=1";
                    $('iframe', self).prop('src',videoURL);
                },
                function () {
                    $('.switch-video', this).fadeOut(duration);
                    $('.base', this).fadeTo(duration, 1);

                    var videoURL = $('iframe', self).prop('src');
                    videoURL = videoURL.replace("&autoplay=1", "");
                    $('iframe', self).prop('src','');
                    $('iframe', self).prop('src',videoURL);
                    $('iframe', self).fadeIn('slow');
                }
            );





        }

    });

    return $.TemplateMonster.switchImage;
});