define(['jquery', 'Manadev_LayeredNavigation/js/vars/actionHelper',
    'Manadev_Core/js/functions/requestAnimationFrame'],
function($, actionHelper, requestAnimationFrame) {

    $(document).on('mana-layered-navigation-action', function(event, action) {
        actionHelper.forEachElement('.filter-current li a, .m-applied-filters li a', action, function(action) {
            var $a = $(this);
            var $li = $a.parent();

            if (action.op == '-' || !action.op && action.clear) {
                $li.hide();
            }
        });
    });

    return function(config, element) {
        $(element).on('click', '.filter-current li a, .filter-clear, .m-applied-filters li a', function() {
            $(document).trigger('mana-layered-navigation-action', [$(this).data('action')]);
        });

        $(element).on('click', '.filter-title', function() {
            $(document).trigger('mana-after-show', [element]);
        });
    };
});