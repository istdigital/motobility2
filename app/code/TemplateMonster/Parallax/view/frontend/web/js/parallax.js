define([
    'jquery',
    'jquery.rd-parallax',
    'jquery.youtubebackground',
    'jquery.vide'
], function ($) {
    'use strict';

    $.widget('tm.parallax', {
        options: {
            staticVideoSelector: '.static-video',
            youtubeVideoSelector: '.youtube-video',
            videDefaultOptions: {
                postType: 'detect',
                loop: true,
                autoplay: true,
                position: '0, 0'
            }
        },

        _create: function() {
            var self = this;
            var elem = this.element;
            $(this.options.staticVideoSelector).each(function() {
                var paths = {
                    mp4: $(this).data('mp4'),
                    webm: $(this).data('webm'),
                    poster: $(this).data('poster')
                };
                $(this).vide(paths, self.options.videDefaultOptions);
            });

            $(this.options.youtubeVideoSelector).each(function() {
                $(this).YTPlayer({
                    fitToBackground: false,
                    videoId: $(this).data('video-id'),
                    width: '100%',
                    playerVars: {
                        wmode: 'transparent',
                    },
                });
            });
            $.RDParallax();
            $('.rd-parallax').css({'visibility' : 'visible'});
        }
    });

    return $.tm.parallax;
});