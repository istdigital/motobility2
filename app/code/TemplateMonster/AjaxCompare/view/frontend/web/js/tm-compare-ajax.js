/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */


define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'mage/translate',
    'mage/loader'
],function($,alert){
    'use strict';

    $.widget('tm.compareItems',$.mage.compareItems,{

        _create: function() {
            this._super();
        },

        _confirm: function(selector, message) {

            if(!compareProductAddAjax) {
                return this._super(selector, message);
            }

            $('body').on('click', selector,function(e) {
                e.preventDefault();
                var agree =  confirm(message);
                if(!agree) {return false;}

                var params = $(e.currentTarget).data('post');

                var postParams = params.data;
                postParams['form_key'] = $('input[name="form_key"]').val();

                 $.ajaxSetup({showLoader: true});
                 $.post(params.action,postParams,function(data){
                    $('body').trigger('contentUpdated');
                 }).fail(function(){
                     alert({
                         content: $.mage.__('Can not finish request. Try again.')
                     });
                 });

                return false;
            });
        }
    });

    return $.tm.compareItems;
});
