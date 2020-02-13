define([
    'jquery',
    'underscore',
    'mage/template',
    'matchMedia',
    'jquery/ui',
    'Magento_Search/js/form-mini'

], function ($, _, mageTemplate, mediaCheck) {
    'use strict';
    /**
     * Check whether the incoming string is not empty or if doesn't consist of spaces.
     *
     * @param {String} value - Value to check.
     * @returns {Boolean}
     */
    function isEmpty(value) {
        return value.length === 0 || value == null || /^\s+$/.test(value);
    }
    $.widget('tm.quickSearchAjax', $.mage.quickSearch,{
        options: {
            template:
                '<li class="<%- data.row_class %> search-item" id="qs-option-<%- data.index %>" role="option">' +
                    //product item
                    '<% if(data.product) { %>' +
                        '<a href="<%- data.url %>">' +
                    '<% if(data.image) { %>' +
                        '<span class="search-thumb">' +
                            '<img src="<%- data.image %>"/>' +
                        '</span>' +
                    '<% } %>' +
                        '<span class="qs-option-name">' +
                            ' <%- data.title %>' +
                        '</span>' +
                            '<%= data.price %>' +
                        '</a>' +
                        //category item
                    '<% } else if(data.category) { %>' +
                        '<a href="<%- data.url %>">' +
                        '<span class="qs-option-name">' +
                            ' <%- data.title %>' +
                        '</span>' +
                        '</a>' +
                        //search item
                    '<% } else { %>' +
                        '<span class="qs-option-name">' +
                            '<%- data.title %>' +
                        '</span>' +
                        '<span aria-hidden="true" class="amount"> (' +
                            '<%- data.num_results %>' +
                        ')</span>' +
                    '<% } %>' +
                '</li>'
       },

       /**
         * Executes when the value of the search input field changes. Executes a GET request
         * to populate a suggestion list based on entered text. Handles click (select), hover,
         * and mouseout events on the populated suggestion list dropdown.
         * @private
         */
        _onPropertyChange: function () {
            var searchField = this.element,
                clonePosition = {
                    position: 'absolute',
                    // Removed to fix display issues
                    // left: searchField.offset().left,
                    // top: searchField.offset().top + searchField.outerHeight(),
                    width: searchField.outerWidth()
                },
                source = this.options.template,
                template = mageTemplate(source),
                dropdown = $('<ul role="listbox"></ul>'),
                value = this.element.val();

            this.submitBtn.disabled = isEmpty(value);

            if (value.length >= parseInt(this.options.minSearchLength, 10)) {
                
                this.element.parent().addClass("ajaxLoading");

                $.getJSON(this.options.url, {
                    q: value
                }, $.proxy(function (data) {
                    this.element.parent().removeClass("ajaxLoading");
                    if (data.length) {
                        $.each(data, function (index, element) {
                            var html;

                            element.index = index;
                            html = template({
                                data: element
                            });
                            dropdown.append(html);
                        });

                        this._resetResponseList(true);

                        this.responseList.indexList = this.autoComplete.html(dropdown)
                            .css(clonePosition)
                            .show()
                            .find(this.options.responseFieldElements + ':visible');

                        this.element.removeAttr('aria-activedescendant');

                        if (this.responseList.indexList.length) {
                            this._updateAriaHasPopup(true);
                        } else {
                            this._updateAriaHasPopup(false);
                        }

                        this.responseList.indexList
                            .on('click', function (e) {
                                this.responseList.selected = $(e.currentTarget);
                                this.searchForm.trigger('submit');
                            }.bind(this))
                            .on('mouseenter mouseleave', function (e) {
                                this.responseList.indexList.removeClass(this.options.selectClass);
                                $(e.target).addClass(this.options.selectClass);
                                this.responseList.selected = $(e.target);
                                this.element.attr('aria-activedescendant', $(e.target).attr('id'));
                            }.bind(this))
                            .on('mouseout', function (e) {
                                if (!this._getLastElement() &&
                                    this._getLastElement().hasClass(this.options.selectClass)) {
                                    $(e.target).removeClass(this.options.selectClass);
                                    this._resetResponseList(false);
                                }
                            }.bind(this));
                    } else {
                        this._resetResponseList(true);
                        this.autoComplete.hide();
                        this._updateAriaHasPopup(false);
                        this.element.removeAttr('aria-activedescendant');
                    }
                }, this));
            } else {
                this._resetResponseList(true);
                this.autoComplete.hide();
                this._updateAriaHasPopup(false);
                this.element.removeAttr('aria-activedescendant');
            }
        }
    });

    return $.tm.quickSearchAjax;

});