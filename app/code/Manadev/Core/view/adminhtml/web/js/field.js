/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

define(['jquery'], function($) {

    return function(config, element) {
        var tempDisable = false;
        $(element).on('input change', '.control input, .control select',function() {
            if (!tempDisable && config.hasDefault) {
                $(element).find('.field-service .use-default input').removeAttr('checked');
            }
        });
        $(element).on('click', '.field-service .use-default',function() {
            if ($(this).find('input').is(':checked')) {
                var $value = $(element).find('.control input');
                if ($value.length) {
                    $value.val(config.defaultValue);
                    tempDisable = true;
                    // Trigger change event for hideShowElements
                    $value.trigger('change');
                    tempDisable = false;
                    return true;
                }

                $value = $(element).find('.control select');
                if ($value.length) {
                    $value.val(config.defaultValue);
                    tempDisable = true;
                    $value.trigger('change');
                    tempDisable = false;
                    return true;
                }
            }
        });
    };
});