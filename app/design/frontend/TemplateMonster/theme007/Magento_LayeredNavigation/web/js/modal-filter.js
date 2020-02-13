/**
 * Copyright © 2015. All rights reserved.
 */

require([
    'jquery',
    'matchMedia',
    'Magento_Ui/js/modal/modal'
], function ($, mediaCheck) {
    "use strict";

    function modalAside(){
        $('.toolbar-products .filter-toggle').css('display', 'inline-block');
        mediaCheck({
            media: '(max-width: 767px)',
            entry: function () {
                $('.filter-toggle, .modal-header').css('display', 'block');
                $('#layered-filter-block .block-title').css('display', 'none');
                $('#layered-filter-block aside').addClass('modal-slide');
                $('.filter-content').modal({
                    "type": "slide",
                    "wrapperClass": "filter-wrapper",
                    "trigger": "[data-trigger=filter]",
                    "parentModalClass": "_has-modal-custom _has-auth-shown",
                    "responsive": true,
                    "responsiveClass": "custom-slide",
                    "overlayClass": "dropdown-overlay modal-custom-overlay",
                    "modalClass": "filter-slide",
                    "buttons": []
                });
            },
            exit: function () {
                $('.filter-toggle, .filter-slide .modal-header').css('display', 'none');
                $('#layered-filter-block .block-title').css('display', 'block');
                $('#layered-filter-block aside').removeClass('modal-slide');
            }
        });
    }

    modalAside();

    $(document).ajaxComplete(function(){
        $('body').removeClass('_has-modal-custom');
        modalAside();
    });

});