/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Search
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define(
    [
        'jquery',
        'Magento_Catalog/js/price-utils',
        'mpDevbridgeAutocomplete'
    ], function ($, priceUtils) {
        'use strict';
        $.widget(
            'mageplaza.search', {
                _create: function () {
                    var categorySelect = $('#mpsearch-category'),
                        searchInput = $('#search'),
                        searchVar = [],
                        self = this;

                    if (categorySelect.length) {
                        categorySelect.on('change', function () {
                            searchInput.focus();

                            if ($(this).val() === 0) {
                                $(this).removeAttr('name');
                            } else {
                                $(this).attr('name', 'cat');
                            }
                        });
                    }

                    if (this.options.isEnableSuggestion === '1') {
                        searchVar = this.sortBy(searchVar);
                    }
                    searchVar = $.extend(true, mp_products_search, searchVar);

                    searchInput.autocomplete({
                        appendTo: ".block-search .block-content .control",
                        lookup: searchVar,
                        lookupLimit: this.options.lookupLimit,
                        maxHeight: 2000,
                        minChars: 0,
                        lookupFilter: function (suggestion, query, queryLowerCase) {
                            var decodeEntities = (self.decodeEntities());

                            /** Category search*/
                            if (categorySelect.length) {
                                var categoryId = categorySelect.val();
                                if (categoryId > 0 && ($.inArray(categoryId, suggestion.c) === -1)) {
                                    return false;
                                }
                            }

                            /** Product Suggestion*/
                            if (query.length === 0) {
                                return suggestion.o !== 'product_search';
                            }
                            /** Product Search*/
                            return suggestion.o === 'product_search' && ((decodeEntities(suggestion.s.toLowerCase()).indexOf(queryLowerCase) !== -1)
                                || (decodeEntities(suggestion.value.toLowerCase()).indexOf(queryLowerCase) !== -1));
                        },
                        onSelect: function (suggestion) {
                            window.location.href = self.correctProductUrl(suggestion.u);
                        },
                        formatResult: function (suggestion, currentValue) {
                            var html = '<a href="' + self.correctProductUrl(suggestion.u) + '">',
                                displayInfo = self.options.displayInfo,
                                priceFormat = self.options.priceFormat;

                            if ($.inArray('image', displayInfo) !== -1) {
                                html += '<div class="suggestion-left"><img class="img-responsive" src="' + self.correctProductUrl(suggestion.i, true) + '" alt="" /></div>';
                            }

                            html += '<div class="suggestion-right">';
                            html += '<div class="product-line product-name">' + suggestion.value + '</div>';

                            if ($.inArray('price', displayInfo) !== -1) {
                                html += '<div class="product-line product-price">' + $.mage.__('Price ') + priceUtils.formatPrice(suggestion.p, priceFormat) + '</div>';
                            }

                            if ($.inArray('description', displayInfo) !== -1 && suggestion.d && suggestion.d.replace('""', '')) {
                                html += '<div class="product-des"><p class="short-des">' + suggestion.d + '</p></div>';
                            }

                            html += '</div></a>';

                            return html;
                        }
                    });
                },

                correctProductUrl: function (urlKey, isImage) {
                    var baseUrl = this.options.baseUrl,
                        baseImageUrl = this.options.baseImageUrl;

                    if (urlKey.search('http') !== -1) {
                        return urlKey;
                    }

                    return ((typeof isImage !== 'undefined') ? baseImageUrl : baseUrl) + urlKey;
                },

                sortBy: function (searchVar) {
                    var sortBy = this.options.sortBy;

                    if (sortBy === 'new_products') {
                        searchVar = mp_new_product_search;
                    } else if (sortBy === 'most_viewed_products') {
                        searchVar = mp_most_viewed_products;
                    } else {
                        searchVar = mp_bestsellers;
                    }

                    return searchVar;
                },

                decodeEntities: function () {
                    var element = document.createElement('div');

                    function decodeHTMLEntities(str) {
                        if (str && typeof str === 'string') {
                            str = str.replace(/<script[^>]*>([\S\s]*?)<\/script>/gmi, '');
                            str = str.replace(/<\/?\w(?:[^"'>]|"[^"]*"|'[^']*')*>/gmi, '');
                            element.innerHTML = str;
                            str = element.textContent;
                            element.textContent = '';
                        }

                        return str;
                    }

                    return decodeHTMLEntities;
                }
            }
        );

        return $.mageplaza.search;
    }
);