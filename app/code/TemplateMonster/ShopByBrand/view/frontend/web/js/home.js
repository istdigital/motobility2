define(['uiComponent','jquery','owlcarousel'], function(Component,$) {
    return Component.extend({
        initialize: function () {
            this._super();
            this.brands = [];
            this.isVisible = false;
            this.observe(['brands','isVisible']);
            this.fetchPosts();
        },
        fetchPosts: function () {
            $.ajax({
                dataType: 'json',
                url: BASE_URL + 'brand/ajax',
                type: 'post',
                success: (function(result) {
                    this.brands(result);
                    this.isVisible(true);

                    $('#homebrand .owl-carousel').owlCarousel({
                        loop:true,
                        margin:10,
                        nav:true,
                        navText:["<i class='icon-arrow-prev'></i>","<i class='icon-arrow-next'></i>"],
                        responsive:{
                            0:{
                                items:1
                            },
                            600:{
                                items:3
                            },
                            1000:{
                                items:4
                            }
                        }
                    });

                    $('#homebrand .owl-carousel').owlCarousel('refresh');

                }).bind(this),
                error: function(){}
            });
        }
    });
});