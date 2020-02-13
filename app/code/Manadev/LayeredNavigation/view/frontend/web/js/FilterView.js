define(['jquery', 'Manadev_LayeredNavigation/js/vars/actionHelper',
    'Manadev_Core/js/functions/requestAnimationFrame'],
function($, actionHelper, requestAnimationFrame) {

    $(document).on('mana-layered-navigation-action', function(event, action) {
        actionHelper.forEachElement('.mana-filter-block .items li a', action, function(action) {
            var $a = $(this);
            var $li = $a.parent();

            if (!action.op) {
                if (action.clear) {
                    $li.removeClass('mana-selected');
                }
                else {
                    $li.addClass('mana-selected');
                }
            }
            else if (action.op == '+') {
                $li.addClass('mana-selected');
                requestAnimationFrame(function () {
                    $a.data('action', '-' + action.param + '=' + action.value);
                });
            }
            else {
                $li.removeClass('mana-selected');
                requestAnimationFrame(function () {
                    $a.data('action', '+' + action.param + '=' + action.value);
                });
            }
        });
    });

    return function(config, element) {
        $(element).on('click', 'li a', function() {
            $(document).trigger('mana-layered-navigation-action', [$(this).data('action')]);
        });
    };
});