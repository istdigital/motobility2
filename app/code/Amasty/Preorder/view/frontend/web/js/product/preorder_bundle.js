define([
    'jquery',
    'Amasty_Preorder/js/product/preorder'
], function($) {
    $.widget('mage.amastyPreorderBundle', $.mage.amastyPreorder, {
            options: {
                map: {},
                optionsData: {},
                isAllProductsPreorder: 0,
                checkedElements: {},
                availabilityElement: $(".product-info-main").first().find('.stock')
            },
            _create: function(){
                this._saveOriginal();
                if(this.options.isAllProductsPreorder == 1) {
                    this.enable();
                }
                var self = this;
                var option;
                for(var optionId in this.options.optionsData) {
                    option = this.options.optionsData[optionId];
                    var $place = $($("#bundle-option-"+optionId+"-qty-input").parents('.field.option')[0]);

                    if(option.isRequired && option.isPreorder && option.isSingle) {
                        self._enableOrDisablePreorder({
                            mapId: optionId + "-" +option.selectionId,
                            optionId: optionId,
                            selectionId: option.selectionId
                        }, $place);
                    }
                }
                $('.bundle-options-wrapper .radio, .bundle-options-wrapper .bundle-option-select')
                    .change(function(event){
                        var element = event.currentTarget;
                        if (element.checked) {
                            var elementInfo = self._getElementInfo(element);
                            var $place = $($(element).parents('.field.option')[0]);
                            self._enableOrDisablePreorder(elementInfo, $place);
                        }
                        return;
                    });

                $('.bundle-options-wrapper .checkbox')
                    .change(function(event){
                        var element = event.currentTarget;
                        var $place = $($(element).parents('.field.option')[0]);
                        var elementInfo = self._getElementInfo(element);
                        var isSelect = $(element).is(":checked")
                        self._enableOrDisablePreorderMultiselection(elementInfo, $place, isSelect);
                        return;
                    });
                $('.bundle-options-wrapper .multiselect')
                    .change(function(event){
                        var element = event.currentTarget;
                        var $place = $($(element).parents('.field.option')[0]);
                        var elementInfo = self._getElementInfo(element);
                        var isSelect;
                        $.each($(element).find('option'), function(key, option){
                            isSelect = false;
                            elementInfo.selectionId = $(option).val();
                            elementInfo.mapId = elementInfo.optionId + "-" + elementInfo.selectionId;
                            $.each($(element).val(), function(valueKey, value){
                                if($(option).val() == value) {
                                    isSelect = true;
                                    return;
                                }
                            });
                            self._enableOrDisablePreorderMultiselection(elementInfo, $place, isSelect);
                        });
                        return;
                    });
            },
            _changeLabels: function() {
                $.mage.catalogAddToCart.prototype.options.addToCartButtonTextDefault = this.options.addToCartLabel;
                this.options.addToCartButton.html(this.options.addToCartLabel);
                this.options.availabilityElement.html(this.options.preOrderNote);
            },
            _getElementInfo: function(element){
                var elementInfo = {
                    mapId: 0,
                    optionId: 0,
                    selectionId: 0
                };
                elementInfo.mapId = element.id.substring(element.id.indexOf("bundle-option-")+String("bundle-option-").length);
                if($(element).prop('tagName').toLowerCase() == "select") {
                    elementInfo.optionId = elementInfo.mapId;
                    elementInfo.selectionId = $(element).val();
                    elementInfo.mapId += "-" + elementInfo.selectionId;
                } else {
                    if(elementInfo.mapId.indexOf("-") > -1) {
                        elementInfo.optionId = elementInfo.mapId.substring(0, elementInfo.mapId.indexOf("-"));
                        elementInfo.selectionId = elementInfo.mapId.substring(elementInfo.mapId.indexOf("-")+1);
                    } else {
                        elementInfo.optionId = elementInfo.mapId;
                    }

                }
                return elementInfo;
            },
            _enableOrDisablePreorder: function (elementInfo, $place) {
                if(this.options.map[elementInfo.mapId]){
                    var $container = $('#bundle-option-'+elementInfo.optionId+'-preorder-note');
                    if($container.length == 0) {
                        $place.append('<div class="field" style="margin-top: 10px"><span id="bundle-option-'+elementInfo.optionId+'-preorder-note">'+this.options.map[elementInfo.mapId].note+'</span></div>');
                    } else {
                        $container.html(this.options.map[elementInfo.mapId].note);
                    }
                    this.options.checkedElements[elementInfo.optionId] = true;
                    this.enable();
                } else {
                    var $container = $('#bundle-option-'+elementInfo.optionId+'-preorder-note');
                    if($container.length > 0) {
                        $container.html('');
                    }
                    this.options.checkedElements[elementInfo.optionId] = false;

                    var counter = 0;

                    for (var key in this.options.checkedElements) {
                        if(this.options.checkedElements[key]) {
                            counter++;
                        }
                    }
                    if(counter == 0) {
                        this.disable();
                    }
                }
            },
            _enableOrDisablePreorderMultiselection: function(elementInfo, $place, isSelect) {
                if(this.options.map[elementInfo.mapId] && isSelect){
                    var $container = $('#bundle-option-'+elementInfo.mapId+'-preorder-note');
                    if($container.length == 0) {
                        $place.append('<div class="field" style="margin-top: 10px"><span id="bundle-option-'+elementInfo.mapId+'-preorder-note">'+this.options.map[elementInfo.mapId].note+'</span></div>');
                    } else {
                        $container.html(this.options.map[elementInfo.mapId].note);
                    }
                    this.options.checkedElements[elementInfo.mapId] = true;
                    this.enable();
                } else {
                    var $container = $('#bundle-option-'+elementInfo.mapId+'-preorder-note');
                    if($container.length > 0) {
                        $container.html('');
                    }
                    this.options.checkedElements[elementInfo.mapId] = false;

                    var counter = 0;

                    for (var key in this.options.checkedElements) {
                        if(this.options.checkedElements[key]) {
                            counter++;
                        }
                    }
                    if(counter == 0) {
                        this.disable();
                    }
                }
            }
        }

    );
});
