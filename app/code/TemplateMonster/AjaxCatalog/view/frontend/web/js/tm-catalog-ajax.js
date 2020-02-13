/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

define([
    'jquery',
    'mage/translate',
    'Magento_Catalog/js/product/list/toolbar',
    'ko',
    'mage/loader'
], function ($, $t, ko) {

    var isLoadTmAjaxCatalog = false;

    $.widget('tm.catalogAjax', $.mage.productListToolbarForm, {

        options: {
            paginationControl: ".pages a",
            layoutNavigation: ".block.filter a:not(.ui-slider-handle)",
            layerMap: {name: 'layer'},
            paginationMap: {name: 'showpagination'},
            activeFilters: {},
            quickView: {},
            catalogAjaxLoading: 'catalog-ajax-loading',
            showLoader: $('body'),
            scrollAnchor: '#infinite_loadMoreAnchor',
            infiniteScrollPages: parseInt($('#infinite_loadMoreAnchor').data('pages')),
            lastPages: parseInt($('#infinite_lastPageNum').data('pages')),
            allProductsSelector: '#toolbar-amount .toolbar-number:last-child',
            minProductsRange: '#toolbar-amount .toolbar-number:first-child',
            allProducts: null,
            scrollProcessing: false,
            windowHeightOffset: 200,
            defaultPage: 1,
            showMoreActions: '.infiniteScroll_wrap',
            emptyProductListText: $.mage.__('We can\'t find products matching the selection.')
        },

        _create: function () {
            // Add ajax handlers for pagination and layer filter

            this._layerPaginationInit();
            this._super();
            this._loadBrand();

            if (this._isEnabledScroll()) {
                this.options.activeFilters.p = 1;
                this._getAllProducts();
                this._infiniteScrollInit();
                this._hidePagination();
            }
        },

        /**
         * Init selectors for layered filters and pagination
         * @private
         */
        _layerPaginationInit: function () {

            //Layer filters intercept
            var actionType = $(this.options.paginationControl).is("select") ? 'change' : 'click';

            $(this.options.paginationControl).on(actionType,
                {paramName: this.options.paginationMap.name},
                $.proxy(this._layerPagination, this)
            );

            //Pagination filtes intercept
            $(this.options.layoutNavigation).on(actionType,
                {paramName: this.options.layerMap.name},
                $.proxy(this._layerPagination, this)
            );
        },

        /**
         * Handler for layered filters and pagination
         * @param event
         * @returns {boolean}
         * @private
         */
        _layerPagination: function (event) {
            if (!this.options.activeFilters[event.data.paramName]) {
                return true;
            }
            event.preventDefault();

            //TODO: need fix for differend layered filter type.In next version!
            var locationURL = $(event.currentTarget).attr('href');

            this._ajaxNavClient(locationURL);
        },

        /**
         * Update url and pass to ajax request
         * @param paramName
         * @param paramValue
         * @param defaultValue
         */
        changeUrl: function (paramName, paramValue, defaultValue) {

            ////Check if need activate ajax filter
            if (!this.options.activeFilters[paramName]) {
                this._super(paramName, paramValue, defaultValue);
                return;
            }

            var urlPaths = this.options.url.split('?');

            //If infinite scroll enabled use window location
            if (this.options.scrollProcessing) {
                urlPaths = window.location.href.split('?');
            }

            var baseUrl = urlPaths[0],
                urlParams = urlPaths[1] ? urlPaths[1].split('&') : [],
                paramData = {},
                parameters;

            for (var i = 0; i < urlParams.length; i++) {
                parameters = urlParams[i].split('=');
                paramData[parameters[0]] = parameters[1] !== undefined
                    ? window.decodeURIComponent(parameters[1].replace(/\+/g, '%20'))
                    : '';
            }

            //Drop infinite scroll if any filter activated
            if (paramName != 'p') {
                delete paramData.p;
                this.options.scrollProcessing = false;
            }

            paramData[paramName] = paramValue;
            if (paramValue == defaultValue) {
                delete paramData[paramName];
            }
            paramData = $.param(paramData);

            var locationURL = baseUrl + (paramData.length ? '?' + paramData : '');

            this._ajaxNavClient(locationURL);
        },

        /**
         * Get URL params as object
         * @returns {{}}
         * @private
         */
        _getUrlParams: function (url) {
            var urlPaths = url.split('?'),
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

            return paramData;
        },

        /**
         * Get number of products in current request and save in options
         * @private
         */
        _getAllProducts: function () {
            this.options.allProducts = parseInt($(this.options.allProductsSelector).html());
        },

        /**
         * Get number of product pages
         * @returns {number}
         * @private
         */
        _getMaxPages: function () {
            var url = window.location.href,
                params = this._getUrlParams(url),
                limit = params.product_list_limit;

            if (limit == undefined) {
                limit = parseInt(this.options.limitDefault);
            }

            return Math.ceil(this.options.allProducts / limit);
        },

        /**
         * Get current page number
         * @returns {Number}
         * @private
         */
        _getCurrentPage: function () {
            var params = this._getUrlParams(window.location.href),
                result = params['p'] ? params['p'] : this.options.defaultPage;

            return parseInt(result);
        },

        /**
         * Hide pagination
         * @private
         */
        _hidePagination: function () {
            $('.pages').hide();
        },

        /**
         * Init infinite scroll
         * @private
         */
        _infiniteScrollInit: function () {

            var self = this,
                options = this.options;
            var countPagesAfterFilters = Math.ceil(this.options.allProducts / parseInt(this.options.limitDefault));

            this._setMinProductsRange();
            if (countPagesAfterFilters <= 1) {
                $(document).off('scroll');
                return false;
            } else  {
                $(document).on('scroll', function () {
                    var windowMarkerPosition = $(this).scrollTop() + ($(window).height() - options.windowHeightOffset),
                        scrollAnchorPosition = $(options.scrollAnchor).position().top;

                    //Check if window scrollTop match anchor position
                    if (scrollAnchorPosition - windowMarkerPosition <= 20) {

                        if (self.options.scrollProcessing) {
                            return false;
                        }
                        if (self._getCurrentPage() >= countPagesAfterFilters) {
                            return false;
                        }

                        if (self._getCurrentPage() > self.options.infiniteScrollPages) {
                            self._loadMoreButton();
                            return false;
                        }
                        self.options.scrollProcessing = true;

                        self.changeUrl('p', self._getCurrentPage() + 1, self.options.defaultPage);
                    }
                })
            }
        },

        /**
         *
         * @returns {boolean}
         * @private
         */
        _isEnabledScroll: function () {
            return $(this.options.scrollAnchor).length > 0
        },

        /**
         *
         * @private
         */
        _loadMoreButton: function () {
            var self = this,
                actions = $(this.options.showMoreActions).find('.actions'),
                button = actions.find('button');

            if (self._getCurrentPage() < self._getMaxPages()) {
                self.options.scrollProcessing = true;
                button.find('.count-primary-pages').text(' (' + (self._getMaxPages() - self._getCurrentPage()) + ')');

                button.on('click', function () {
                    self.changeUrl('p', self._getCurrentPage() + 1, self.options.defaultPage);
                });

                actions.show();
            } else {
                actions.hide();
            }
        },
        _loadBrand: function () {
            var sidebarBrand = $('#layered-filter-block .filter-current--wrapper .items .item').size();

            if ($('body.brand-brand-view').length) {
                if(sidebarBrand <= 1 ) {
                    console.log (sidebarBrand);
                    $('.filter-current--wrapper .filter-actions, .filter-current--wrapper .action.remove').css({'display':'none'});
                } else {

                }
            }

        },

        /**
         * Return and insert html by ajax request
         * @param locationURL
         * @returns {boolean}
         * @private
         */
        _ajaxNavClient: function (locationURL) {

            var self = this;

            if (isLoadTmAjaxCatalog) {
                return false;
            }

            isLoadTmAjaxCatalog = true;

            var encodeUrl = decodeURIComponent(locationURL);


            var parser;
            parser = document.createElement('a');
            parser.href = locationURL;

            var pathName = parser.pathname;
            var search = parser.search;
            var pathSearch = pathName + search;
            var pathSearchEncode = decodeURIComponent(pathSearch);

            if (pathSearchEncode) {
                window.history.pushState({"path": pathSearchEncode}, "Ajax Search", pathSearchEncode);
            }
            self._loadBrand();
            $.ajax(encodeUrl, {
                method: 'POST',
                showLoader: this.options.showLoader.loader('show')
            }).done(function (data) {

                if (data.error) {
                    if ((typeof data.message) == 'string') {
                        alert(data.message);
                    } else {
                        alert(data.message.join("\n"));
                    }
                    return false;
                }

                var contentHtml = data.content ? data.content : data;

                if (contentHtml) {
                    self._updateProducts(contentHtml);
                    // self._updatePosts(contentHtml);
                }

                self._getAllProducts();
                // console.log(self._getAllProducts());

                var layerHtml = data.layer;
                if (layerHtml) {
                    $('#layered-filter-block').replaceWith(
                        $('<div />').html(layerHtml).find('#layered-filter-block')
                    );
                }

                /*@overwrite function in SwatchRenderer.js*/
                $.parseParams = function (query) {
                    var re = /([^&=]+)=?([^&]*)/g,
                        decodeRE = /\+/g, /*Regex for replacing addition symbol with a space*/
                        decode = function (str) {
                            return decodeURIComponent(str.replace(decodeRE, " "));
                        },
                        params = {}, e;

                    while (e = re.exec(query)) {
                        var k = decode(e[1]), v = decode(e[2]);
                        if (k.substring(k.length - 2) === '[]') {
                            k = k.substring(0, k.length - 2);
                            (params[k] || (params[k] = [])).push(v);
                        }
                        else params[k] = v;
                    }
                    return params;
                };

                var result = $.parseParams(parser.search.substring(1));
                /*@overwrite function in SwatchRenderer.js
                 when SR Swatcher try change color after filter apply*/
                $.parseParams = function () {
                    return result;
                };

                /*REFRESH ALL WIDGETS*/
                $('body').trigger('contentUpdated');

                if (require.defined("quickViewButton")) {
                    $('.toolbar.toolbar-products').first().quickViewButton({
                        buttonText: self.options.quickView.buttonText,
                        buttonCssClass: self.options.quickView.buttonCssClass
                    })
                }
                if (require.defined("catalogAddToCart")) {
                    $("form[data-role='tocart-form']").catalogAddToCart();
                }

                if (self._isEnabledScroll() === false) {
                    if ($('.toolbar-products').length) {
                        $("html, body").animate({scrollTop: $('.toolbar-products').offset().top - 100}, 1000);
                    }
                }
            }).fail(function () {
                alert($.mage.__('Can not finish request. Try again.'));
            }).always(function () {
                isLoadTmAjaxCatalog = false;
            });

        },

        /**
         *
         * @param contentHtml
         * @private
         */
        _updateProducts: function (contentHtml) {

            $('.toolbar.toolbar-products').replaceWith(
                $('<div />').html(contentHtml).find('.toolbar.toolbar-products').first()
            );

            if (this.options.scrollProcessing) {
                var productList = $('<div />').html(contentHtml).find('.product-items, .block-posts-list').html();
                var existingProducts = $('.products.wrapper .product-items, .block-posts-list .post-item').html();

                $('.products.wrapper .product-items, .block-posts-list .post-item').append(productList);
            } else {
                var listingContent = $('<div />').html(contentHtml).find('.product-items, .block-posts-list').html();
                if (listingContent) {
                    $('.products.wrapper, .block-posts-list').replaceWith(
                        $('<div />').html(contentHtml).find('.products.wrapper, .block-posts-list')
                    );
                } else {
                    $('.products.wrapper, .block-posts-list').replaceWith(
                        $('<div />').html(
                            '<div class="message info empty"><div>' +
                            this.options.emptyProductListText +
                            '</div></div>'
                        )
                    );
                }
            }
        },

        // _updatePosts: function(contentHtml){
        //
        //     $('.toolbar.toolbar-products').replaceWith(
        //         $('<div />').html(contentHtml).find('.toolbar.toolbar-products').first()
        //     );
        //
        //     if(this.options.scrollProcessing){
        //         var productList = $('<div />').html(contentHtml).find('.block-posts-list').html();
        //         var existingProducts = $('.block-posts-list .post-item').html();
        //
        //         $('.block-posts-list .post-item').append(productList);
        //     } else  {
        //         var listingContent = $('<div />').html(contentHtml).find('.block-posts-list').html();
        //         if(listingContent) {
        //             $('.block-posts-list').replaceWith(
        //                 $('<div />').html(contentHtml).find('.block-posts-list')
        //             );
        //         } else {
        //             $('.block-posts-list').replaceWith(
        //                 $('<div />').html(
        //                     '<div class="message info empty"><div>' +
        //                     this.options.emptyProductListText +
        //                     '</div></div>'
        //                 )
        //             );
        //         }
        //     }
        // },

        /**
         *
         * @private
         */
        _setMinProductsRange: function () {
            var firstRange = this.options.minProductsRange;

            $(firstRange).html();
        }
    });

    return $.tm.catalogAjax;
});

