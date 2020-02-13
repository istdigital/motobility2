define(['uiComponent','jquery'], function(Component,$) {
    return Component.extend({
        initialize: function () {
            this._super();
            this.posts = [];
            this.isVisible = false;
            this.observe(['posts','isVisible']);
            this.fetchPosts();
        },
        fetchPosts: function () {
            $.ajax({
                dataType: 'json',
                url: BASE_URL + 'mpblog/ajax',
                type: 'post',
                success: (function(result) {
                    $('#'+this.name).find(".ph-item").remove();
                    this.posts(result);
                    this.isVisible(true);
                }).bind(this),
                error: function(){}
            });
        }
    });
});