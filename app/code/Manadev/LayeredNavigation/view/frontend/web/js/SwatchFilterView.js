define(['jquery', 'Manadev_LayeredNavigation/js/vars/actionHelper',
    'Manadev_Core/js/functions/requestAnimationFrame'],
function($, actionHelper, requestAnimationFrame) {

    $(document).on('mana-layered-navigation-action', function(event, action) {
        actionHelper.forEachElement('.swatch-attribute.swatch-layered  a', action, function(action) {
            var $a = $(this);
            var $swatchOption = $a.find('.swatch-option');

            if (action.op == '+') {
                $a.addClass('mana-selected');
                $swatchOption.addClass('selected');
                requestAnimationFrame(function () {
                    $a.data('action', '-' + action.param + '=' + action.value);
                });
            }
            else {
                $a.removeClass('mana-selected');
                $swatchOption.removeClass('selected');
                requestAnimationFrame(function () {
                    $a.data('action', '+' + action.param + '=' + action.value);
                });
            }
        });
    });

    return function(config, element) {
        $(element).on('click', 'a', function() {
            $(document).trigger('mana-layered-navigation-action', [$(this).data('action')]);
        });
    };
});