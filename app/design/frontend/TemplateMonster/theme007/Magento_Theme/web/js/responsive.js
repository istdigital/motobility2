/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'matchMedia',
    'mage/tabs',
    'domReady!',
    'Magento_Theme/js/device.min'
], function ($, mediaCheck) {
    'use strict';

    mediaCheck({
        media: '(min-width: 768px)',
        // Switch to Desktop Version
        entry: function () {
            (function () {
                var productInfoMain = $('.product-info-main'),
                    productInfoAdditional = $('#product-info-additional');

                if (productInfoAdditional.length) {
                    productInfoAdditional.addClass('hidden');
                    productInfoMain.removeClass('responsive');
                }

            })();

            var galleryElement = $('[data-role=media-gallery]');

            if (galleryElement.length && galleryElement.data('mageZoom')) {
                galleryElement.zoom('enable');
            }

            if (galleryElement.length && galleryElement.data('mageGallery')) {
                galleryElement.gallery('option', 'disableLinks', true);
                galleryElement.gallery('option', 'showNav', false);
                galleryElement.gallery('option', 'showThumbs', true);
            }

            //setTimeout(function () {
            //    $('.product.data.items').tabs('option', 'openOnFocus', true);
            //}, 500);
        },
        // Switch to Mobile Version
        exit: function () {
            $('.action.toggle.checkout.progress')
                .on('click.gotoCheckoutProgress', function () {
                    var myWrapper = '#checkout-progress-wrapper';
                    scrollTo(myWrapper + ' .title');
                    $(myWrapper + ' .title').addClass('active');
                    $(myWrapper + ' .content').show();
                });

            $('body')
                .on('click.checkoutProgress', '#checkout-progress-wrapper .title', function () {
                    $(this).toggleClass('active');
                    $('#checkout-progress-wrapper .content').toggle();
                });

            var galleryElement = $('[data-role=media-gallery]');

            setTimeout(function () {
                if (galleryElement.length && galleryElement.data('mageZoom')) {
                    galleryElement.zoom('disable');
                }

                if (galleryElement.length && galleryElement.data('mageGallery')) {
                    galleryElement.gallery('option', 'disableLinks', false);
                    galleryElement.gallery('option', 'showNav', true);
                    galleryElement.gallery('option', 'showThumbs', false);
                }
            }, 2000);

            setTimeout(function () {
                $('.product.data.items').tabs('option', 'openOnFocus', false);
            }, 500);
            $('.data.item.title').click(function(evt) {
                var _tabTitle = $(this);
                setTimeout(function () {
                    var _thisOffset = $(_tabTitle).offset().top - 56;
                    evt.preventDefault();

                    $('html, body').stop().animate({
                        scrollTop: _thisOffset,
                    }, 300);
                }, 200);
            });
        }
    });
});
