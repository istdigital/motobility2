define(['jquery', 'jquery/ui', "Magento_Ui/js/modal/modal", "mage/adminhtml/wysiwyg/widget"], function ($) {
    'use strict';

    $.widget('tm.configurator', {
        elements: {},
        counter: 1,
        productsModal: null,
        targetButton: null,
        _create: function () {
            //console.log(this.options);
            this._load();
            this.options.staticBlocks = JSON.parse(this.options.staticBlocks);
            /*this.options.subcategories = JSON.parse(this.options.subcategories);*/
            var self = this;
            this.hideElements();
            this.elements.content = this.element.find('#mm_content');
            this.elements.content.sortable({
                update: function () {
                    self.updateData();
                }
            });
            this.elements.addRowButton = this.element.find('#mm_add_row')
            this._bind();

            this._sortInit();
        },
        _load: function () {
            var self = this;
            $.ajax({
                url: this.options.url,
                data: {form_key: window.FORM_KEY, entity: 'products'},
                type: 'POST'
            }).done(function (data) {

                self.options.productsWidget = data;
                $.ajax({
                    url: self.options.url,
                    data: {form_key: window.FORM_KEY, entity: 'subcategories'},
                    type: 'POST'
                }).done(function (data) {
                    self.options.subcategories = data;
                    self.applyData();
                });
            });
        },
        _sortInit: function () {
            var self = this;
            $('#mm_content .mm_row_content').sortable({
                connectWith: '.mm_row_content',
                cursor: 'move',
                update: function () {
                    self.updateData();
                }
            });
        },
        _bind: function () {
            var self = this;

            this.elements.addRowButton.on('click', function () {
                self.addRow();
            });
            this.elements.content.on('click', '.mm_add_button', function (e) {
                self.addColumnButtonClick(e);
            });
            this.elements.content.on('click', '.mm_remove_button', function (e) {
                $(e.target).closest('.mm_row').remove();
                self.updateData();
            });
            this.elements.content.on('click', '.mm_remove_column_button', function (e) {
                $(e.target).closest('.mm_column').remove();
                self.updateData();
            });
            this.elements.content.on('change', '.mm_column_width_select', function (e) {
                var column = $(e.target).closest('.mm_column');
                column.removeAttr('class').addClass('mm_column col-' + e.target.value);
                self.updateData();
            });
            this.elements.content.on('change', '.mm_css_class', function (e) {
                self.updateData();
            });
            this.elements.content.on('click', '.add_from_select', function (e) {
                var select = $(e.target).closest('.mm_data_input').find('.mm_data_input_control');
                var selectedValue = select.val();
                var selectedText = select.find('option:selected').text();
                var multiselect = $(e.target).closest('.mm_column_inner').find('.mm_multiselect');
                multiselect.append($('<option>', {
                    value: selectedValue,
                    text: selectedText
                }));
                self.updateData();
            });
            this.elements.content.on('click', '.mm_remove_selected', function (e) {
                $(e.target).closest('.mm_data_input')
                    .find('.mm_multiselect option:selected')
                    .each(function (i, selected) {
                        $(selected).remove();
                    });
                self.updateData();
                $(e.target).blur();
            });
            this.elements.content.on('click', '.mm_up', function (e) {
                $(e.target).closest('.mm_data_input')
                    .find('.mm_multiselect option:selected')
                    .each(function() {
                        var el = $(e.target).closest('.mm_data_input').find('.mm_multiselect option');
                        var newPos = el.index(this) - 1;
                        if (newPos > -1) {
                            el.eq(newPos).before("<option value='"+$(this).val()+"' selected='selected'>"+$(this).text()+"</option>");
                            $(this).remove();
                        }
                    });
                self.updateData();
                $(e.target).blur();
            });
            this.elements.content.on('click', '.mm_down', function (e) {
                var el = $(e.target).closest('.mm_data_input').find('.mm_multiselect option');
                var countOptions = el.size();
                $(e.target).closest('.mm_data_input')
                    .find('.mm_multiselect option:selected')
                    .each( function() {
                        var newPos = el.index(this) + 1;
                        if (newPos < countOptions) {
                            el.eq(newPos).after("<option value='"+$(this).val()+"' selected='selected'>"+$(this).text()+"</option>");
                            $(this).remove();
                        }
                    });
                self.updateData();
                $(e.target).blur();
            });
            this.elements.content.on('click', '.mm_add_products', function (e) {
                self.addProducts(e);
            });
            this.elements.content.on('click', '.mm_add_widget', function (e) {
                self.addWidget(e);
            });
            this.elements.content.on('click', '.mm_add_video', function (e) {var multiselect = $(e.target).closest('.mm_column_inner').find('.mm_multiselect');
                //console.log($(e.target).closest('.mm_data_input').find('.mm_video'));
                multiselect.append($('<option>', {
                    value: 'video:' + $(e.target).closest('.mm_data_input').find('.mm_video').val(),
                    text: 'Video'
                }));
                self.updateData();
            });
        },
        updateData: function (e) {
            /* this method is needed because of firefox, it's not possible to collect data on submit  */
            var collectedData = [];
            var rows = $('#mm_content .mm_row');
            for (var i = 0; i < rows.length; i++) {
                var rowData = [];
                var columns = $(rows[i]).find('.mm_column');
                for (var j = 0; j < columns.length; j++) {
                    var column = $(columns[j]);
                    var columnWidth = column.find('.mm_column_width_select').first().val();
                    var columnCssClass = column.find('.mm_css_class').first().val();
                    var columnMultiselectOptions = column.find('.mm_multiselect option');
                    var multiselectOptions = [];
                    for (var k = 0; k < columnMultiselectOptions.length; k++) {
                        multiselectOptions.push({
                            'value': $(columnMultiselectOptions[k]).attr('value'),
                            'text': $(columnMultiselectOptions[k]).text()
                        });
                    }
                    var columnData = {
                        'width': columnWidth,
                        'css_class': columnCssClass,
                        'entities': multiselectOptions
                    };
                    rowData.push(columnData);
                }
                collectedData.push(rowData);
            }
            $('#mm_configurated_data').val(JSON.stringify(collectedData));
        },
        hideElements: function () {
            var turnOnOffSelect = $('#container input[name*=mm_turn_on]');
            if (this.options.level == 2) {
                $('input[name=mm_image]').closest('.admin__field').hide();
            }
            if (this.options.level != 4) { // 4
                $('input[name=mm_show_subcategories]').closest('.admin__field').hide();
                $('input[name=mm_number_of_subcategories]').closest('.admin__field').hide();
                //console.log($('input[name=mm_view_mode]'));
                $('select[name=mm_view_mode]').closest('.admin__field').hide();
            }
            if (this.options.level == 1) {
                $('div[data-index=megamenu] .admin__fieldset-wrapper-content').hide();
                $('div[data-index=megamenu] .admin__collapsible-title').css({'cursor':'no-drop'});
            }
            if (this.options.level > 2) {
                turnOnOffSelect.closest('.admin__field').hide();
                $('#mm_configurator').hide();
                //$('div[data-index=mm_css_class]').hide();
            }
            var hideShow = function () {
                $('#mm_configurator').toggle();
                //$('div[data-index=mm_label]').toggle();
                //$('div[data-index=mm_css_class]').toggle();
            };
            if (turnOnOffSelect.val() == 0 && this.options.level == 2) {
                hideShow();
            }
            turnOnOffSelect.change(hideShow);
        },
        addRow: function () {

            var row = $(
                '<div class="mm_row">' +
                '<div class="mm_row_buttons">' +
                '<div class="mm_add_column_div">' +
                '<button class="mm_add_button mm_add" type="button">Add column(s)</button>' +
                '</div>' +
                '<div class="mm_remove_row_div">' +
                '<button class="mm_remove_button mm_remove" type="button">Remove row</button>' +
                '</div>' +
                '</div>' +
                '<ul class="mm_row_content">' +
                '</ul>' +
                '</div>'
            );
            row.appendTo(this.elements.content);
            this._sortInit();
            this.updateData();
            return row;
        },
        getWidthSelectHtml: function (width) {
            var html = '<select class="mm_data_input_control mm_column_width_select admin__control-select">';
            for (var i = 2; i < 13; i++) {
                if (i == width) {
                    html += '<option value="' + i + '" selected="true">col-' + i + '</option>';
                } else {
                    html += '<option value="' + i + '">col-' + i + '</option>';
                }
            }
            html += '</select>';
            return html;
        },

        addColumn: function (row, data) {
            var rowContent = row.find('.mm_row_content').first();
            var cssClass = '';
            if (typeof data.css_class != 'undefined') {
                cssClass = data.css_class;
            }
            var html = '' +
                '<li class="mm_column col-' + data.width + '">' +
                '<div class="mm_column_inner">' +
                '<div class="mm_data_input">' +
                '<label>Set column width</label>' +
                this.getWidthSelectHtml(data.width) +
                '</div>' +
                '<div class="mm_data_input">' +
                '<label>Enter specific class</label>' +
                '<input type="text" class="mm_data_input_control mm_css_class" value="' + cssClass + '" />' +
                '</div>' +
                '<div class="mm_data_input">' +
                '<label>Add subcategory</label>' +
                '<select class="mm_data_input_control admin__control-select">';
            for (var i = 0; i < this.options.subcategories.length; i++) {
                html += $('<option>').
                html(this.options.subcategories[i].name).
                val('subcat:' + this.options.subcategories[i].id).prop('outerHTML');
            }
            html += '</select>' +
                '<button class="mm_add add_from_select" type="button">Add</button>' +
                '</div>' +
                '<div class="mm_data_input">' +
                '<label>Select static block</label>' +
                '<select class="mm_data_input_control admin__control-select">';
            for (var i = 0; i < this.options.staticBlocks.length; i++) {
                html += $('<option>').
                html(this.options.staticBlocks[i].label).
                val('block:' + this.options.staticBlocks[i].value).prop('outerHTML');
            }
            html += '</select>' +
                '<button class="mm_add add_from_select" type="button">Add</button>' +
                '</div>' +
                '<div class="mm_data_input">' +
                '<label>Add product(s)</label>' +
                '<button class="mm_add mm_add_products" type="button">Add</button>' +
                '</div>' +
                '<div class="mm_data_input">' +
                '<label>Add widget</label>' +
                '<button class="mm_add mm_add_widget" type="button">Add</button>' +
                '</div>' +
                '<div class="mm_data_input">' +
                '<label>Add video (insert embedded code including &lt;iframe&gt;)</label>' +
                '<input type="text" class="mm_data_input_control mm_video" />' +
                '<button class="mm_add mm_add_video" type="button">Add</button>' +
                '</div>' +
                '<div class="mm_data_input">' +
                '<div class="mm_select_wrapper">' +
                '<label>Selected Items</label>' +
                '<select class="mm_data_input_control mm_multiselect admin__control-multiselect" multiple="multiple" style="height: 130px; max-width: 100%; resize:none;">' +
                '</select>' +
                '<button class="mm_up" type="button">▲</button>' +
                '<button class="mm_down" type="button">▼</button>' +
                '</div>' +
                '<button class="mm_remove_selected" type="button">Remove selected</button>' +
                '</div>' +
                '<div class="mm_remove_column_div">' +
                '<button class="mm_remove_column_button mm_remove" type="button">Remove column</button>' +
                '</div>' +
                '</div>' +
                '</li>';
            var column = $(html);
            rowContent.append(column);
            this.updateData();
            return column;
        },

        addProducts: function (e) {
            var self = this;

            self.targetButton = e.target;

            var addProductsToMultiselect = function (products) {

                var productsObject = $.parseJSON(products);
                var ids = [];
                $.each(productsObject, function (key, value) {
                    ids.push(key);
                });
                var multiselect = $(self.targetButton).closest('.mm_column_inner').find('.mm_multiselect');

                multiselect.append($('<option>', {
                    value: 'products:' + products,
                    text: 'Products - ' + ids.join()
                }));
                self.updateData();
            };

            if(null == self.productsModal){
                self.productsModal = $('<div id="mm_modal_product_content">' + this.options.productsWidget + '</div>').modal({
                    title: 'Select product(s)',
                    modalClass: 'magento',
                    clickableOverlay: false,
                    buttons: [{
                        text: 'Ok',
                        click: function () {
                            addProductsToMultiselect($("#in_megamenu_products").val());
                            this.closeModal();
                        }
                    },
                        {
                            text: 'Cancel',
                            click: function () {
                                this.closeModal();
                            }
                        }]
                });
            }
            self.productsModal.modal('openModal');

        },
        addWidget: function (e) {
            var self = this;
            var addWidgetToMultiselect = function (value) {
                var multiselect = $(e.target).closest('.mm_column_inner').find('.mm_multiselect');
                multiselect.append($('<option>', {
                    value: 'widget:' + value,
                    text: 'Widget'
                }));
                self.updateData();

            };
            var modal = $('<div id="mm_modal_widget"><textarea rows="10" class="textarea" id="widget_content' + self.counter + '" title="" name="content" style="width:100%; max-height:100%; resize:none;" aria-hidden="true"></textarea></div>').modal({
                title: 'Add widget',
                modalClass: 'magento',
                clickableOverlay: false,
                buttons: [{
                    text: 'Ok',
                    click: function () {
                        addWidgetToMultiselect($("#widget_content"+ self.counter).val());
                        this.closeModal();
                        self.counter++;
                    }
                },
                    {
                        text: 'Cancel',
                        click: function () {
                            this.closeModal();
                            self.counter++;
                        }
                    }]
            });
            modal.modal('openModal');
            widgetTools.openDialog(self.options.baseUrl + this.options.adminFrontName + '/admin/widget/index/widget_target_id/widget_content' + self.counter);
        },
        addColumnButtonClick: function (e) {
            var callBack = {
                thisWidget: this,
                row: $(e.target).closest('.mm_row'),
                onConfirm: function (value) {
                    this.thisWidget.addColumn(this.row, {width: value});
                }
            };
            var modal = $('<div class="mm_modal_content">' + this.getWidthSelectHtml() + '</div>').modal({
                title: 'Column width class(2-12)',
                modalClass: 'magento',
                clickableOverlay: false,
                buttons: [{
                    text: 'Add',
                    click: function () {
                        callBack.onConfirm(this.element.find('.mm_column_width_select').first().val());
                    }
                },
                    {
                        text: 'Close',
                        click: function () {
                            this.closeModal();
                        }
                    }]
            });
            modal.modal('openModal');
        },
        addToColumnMultiselect: function (column, _value, _text) {
            column.find('.mm_multiselect').append($('<option>', {
                value: _value,
                text: _text
            }));
        },
        applyData: function () {
            var data = this.options.data;
            if (typeof data !== 'undefined') {
                var data = JSON.parse(data);
                for (var i = 0; i < data.length; i++) {
                    var row = this.addRow();
                    for (var j = 0; j < data[i].length; j++) {
                        var column = this.addColumn(row, data[i][j]);
                        for (var k = 0; k < data[i][j].entities.length; k++) {
                            this.addToColumnMultiselect(column, data[i][j].entities[k].value, data[i][j].entities[k].text);
                        }
                    }
                }
            }
        }
    });
    return $.tm.configurator;
});