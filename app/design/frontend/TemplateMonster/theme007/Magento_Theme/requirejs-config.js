/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    config: {
        mixins: {
            'mage/validation': {
                'Magento_Theme/js/validation-mixin': true
            }
        }
    },
    map: {
        '*': {
            "theme": 'js/theme',
            "themeChild": 'js/theme-child',
            "selectize":    "js/selectize",
            "googleMapOptions": "js/googleMapOptions"
        }
    },
    paths: {
        "carouselInit":     'js/carouselInit',
        "lozad":     'js/lozad.min',
        "blockCollapse":    'js/sidebarCollapse',
        "animateNumber":    'js/jquery.animateNumber.min',
        "rdnavbar":         'Magento_Theme/js/jquery.rd-navbar',
        "owlcarousel":      'Magento_Theme/js/owl.carousel',
        "magnificPopup":      'Magento_Theme/js/jquery.magnific-popup',
        "customSelect":     "Magento_Theme/js/select2",
        "doubleTap":        "Magento_Theme/js/doubletaptogo",
        "expertScroll":    "Magento_Theme/js/scroll",
        "googleMapOptions": "js/googleMapOptions"
    },
    shim: {
        "rdnavbar":         ["jquery"],
        "owlcarousel":      ["jquery"],
        "magnificPopup":      ["jquery"],
        "doubleTap":      ["jquery"],
        "animateNumber":    ["jquery"]
    },
    deps: [
        "jquery",
        "jquery/jquery.mobile.custom",
        "mage/common",
        "mage/dataPost",
        "mage/bootstrap",
        "Magento_Theme/js/responsive"
    ]
};