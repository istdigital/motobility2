/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
define([
    'jquery',
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    'underscore',
    'Magento_Ui/js/modal/modal',
    'catalogAddToCart',
    'mage/loader'
],function($,$t,alert,_) {
    'use strict';

    var isLoadCompareAjax = false;

    $.widget('tm.showCompareProduct',{
        options: {
            loaderTemplate:
                '<div class="loading-mask" data-role="loader">' +
                    '<div class="loader ajax-compare">' +
                        '<img alt="<%- data.texts.imgAlt %>" src="<%- data.icon %>">' +
                        '<p><%- data.texts.loaderText %></p>' +
                    '</div>' +
                '</div>'
        },
        productInCompareList: [],
        compareProductBox: null,
        prevTemplate: null,
        prevIcon: null,

        _create: function() {
            if(!compareProductAddAjax) {
                return false;
            }
            this._initProductCompareListArray();
            this._actionAddToCompare();
            this._actionCompare();
            this._actionPrint();
            this._actionRemoveProduct();
            _.bindAll(this, '_changeLoaderTemplate', '_recoverLoaderTemplate');
        },

        _actionCompare : function(){
            var self = this;
            $('body').on('click','.action.compare',function(e){
                e.preventDefault();

                var href = $(e.currentTarget).attr('href');

                $.ajaxSetup({showLoader: true});
                //$.ajax({
                //    method: 'POST',
                //    url: href,
                //    beforeSend: self._changeLoaderTemplate,
                //    complete:  self._recoverLoaderTemplate
                //}).done(function(data){
                //    self.compareProductBox = $('#productComparePopup').html(data.content).modal();
                //    self.compareProductBox.modal('openModal');
                //}).fail(function(){
                //    alert({
                //        content: $t('Can not finish request.Try again.')
                //    });
                //});
                //return false;
                $.get(href, onAjaxSuccess)
                    .error(function() {
                        alert({
                            content: $t('Can not finish request.Try again33.')
                        })
                    });
                function onAjaxSuccess(data)
                {
                    self._changeLoaderTemplate;
                    self.compareProductBox = $('#productComparePopup').html(data.content).modal();
                    self.compareProductBox.modal('openModal');
                }





            });
        },

        _actionPrint : function(){
            //Print compare product
            $('body').on('click', '.action.print',function(e) {
                e.preventDefault();
                window.print();
            });

        },

        _actionRemoveProduct : function(){
            //  Add event for remove in modal compare product
            var self = this;
            $('body').on('click','.cell.remove.product',function(e){

                if(isLoadCompareAjax) {return false;}
                isLoadCompareAjax = true;

                e.preventDefault();

                var index;
                index = $( ".cell.remove.product" ).index( this );
                var params = $(e.currentTarget).children('a').data('post');
                var postParams = params.data;

                postParams['form_key'] = $('input[name="form_key"]').val();

                $.ajaxSetup({showLoader: true});
                $.ajax({
                    method: 'POST',
                    url: params.action,
                    data: postParams,
                    beforeSend: self._changeLoaderTemplate,
                    complete:  self._recoverLoaderTemplate
                }).success(function() {
                    ++index; // Psevdo class nth-child starts count from 1
                    ++index; // Add Label product column
                    // Remove product information from modal window
                    $('#product-comparison .cell.product.attribute:nth-child(' + index + ')').remove();
                    $('#product-comparison .cell.product.info:nth-child(' + index + ')').remove();
                    $('#product-comparison .cell.remove.product:nth-child(' + index + ')').remove();

                    if ($('#product-comparison .cell.product.info').length < 1) {
                        self.compareProductBox.modal('closeModal');
                    }
                }).fail(function() {
                    alert({
                        content: $.mage.__('Can not finish request. Try again.')
                    });
                }).always(function() {
                    isLoadCompareAjax = false;
                });
                return false;
            });
        },

        _actionAddToCompare : function(){
            $('body').on('click','a[data-post].tocompare', $.proxy(function(e){

                e.preventDefault();

                var params = $(e.currentTarget).data('post');
                var postParams = params.data;

                //Check if product is in compare list already.
                if($.inArray(postParams.product,this.productInCompareList) !== -1) {
                    alert({
                        content: $.mage.__('Current product is already in the comparison list.')
                    });
                    return false;
                }

                postParams['form_key'] = $('input[name="form_key"]').val();

                $.ajaxSetup({showLoader: true});
                $.ajax({
                    method: 'POST',
                    url: params.action,
                    data: postParams,
                    beforeSend: this._changeLoaderTemplate,
                    complete:  this._recoverLoaderTemplate
                }).done(function(data) {
                    alert({
                        title: $.mage.__('Ajax Compare'),
                        content: data.message
                    });
                    $('body').trigger('contentUpdated');
                }).fail(function() {
                    alert({
                        content: $.mage.__('Can not finish request. Try again.')
                    });
                });
                return false;
            },this));
        },

        _initProductCompareListArray: function()
        {
            //On init widget create array.
            this._getProductCompareArray();
            //Create array on ajax action.
            $(document).on('ajaxComplete', $.proxy(this._getProductCompareArray, this));
        },

        _getProductCompareArray: function() {
            $('[data-role=tocart-form]').catalogAddToCart();
            var storage = $.localStorage;
            var cacheStorage = storage.get('mage-cache-storage');
            if(cacheStorage.hasOwnProperty('compare-products')) {
                var compareProduct = cacheStorage['compare-products'];
                this.productInCompareList = [];
                if(compareProduct.count !== 0) {
                    $.each(compareProduct.items,$.proxy(function(index,value){
                        if($.inArray(value.id,this.productInCompareList) === -1) {
                            this.productInCompareList.push(value.id);
                        }
                    },this));
                }
            }
        },

        _changeLoaderTemplate: function() {
            var ctx = $('[data-container=body]');
            this.prevTemplate = ctx.loader('option', 'template');
            this.prevIcon = ctx.loader('option', 'icon');
            try {ctx.loader('destroy')} catch (e) {}
            ctx.loader({
                icon: this.prevIcon,
                template: this.options.loaderTemplate
            });
        },

        _recoverLoaderTemplate: function () {
            var ctx = $('[data-container=body]');
            try {ctx.loader('destroy')} catch (e) {}
            ctx.loader({
                icon: this.prevIcon,
                template: this.prevTemplate
            });
        }
    });

    return $.tm.showCompareProduct;

});