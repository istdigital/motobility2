define([
    'jquery'
], function ($) {
    'use strict';

    $.widget('TemplateMonster.featuredSwitchImage', {

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
                    $('.base', this).stop().fadeTo(duration, 0);
                    $('.replaced', this).stop().fadeIn(duration);
            },
                function () {
                    $('.replaced', this).stop().fadeOut(duration);
                    $('.base', this).stop().fadeTo(duration, 1);
                }
            );
        },

        _playVideo: function() {
            if(!$(this.element).hasClass('is-video')) return false;

            console.log('222');

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

    return $.TemplateMonster.featuredSwitchImage;
});