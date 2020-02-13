define(['uiComponent','jquery','owlcarousel'], function(Component,$) {
    return Component.extend({
        initialize: function () {
            this._super();
            this.fetchProducts();
        },
        fetchProducts: function () {
            $.ajax({
                url: BASE_URL + 'tmfeatured/ajax',
                type: 'post',
                data: {'type':this.name},
                success: (function(htm) {
                    $('#'+this.name).html(htm);
                    $('#'+this.name+" .owl-carousel").owlCarousel({
                        stagePadding: 5,
                        loop:true,
                        margin:40,
                        nav:true,
                        navText:["<i class='icon-arrow-prev'></i>","<i class='icon-arrow-next'></i>"],
                        responsive:{
                            0:{
                                items:1,
                                nav:false
                            },
                            414:{
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
                    $('#'+this.name+" .owl-carousel").owlCarousel('refresh');
                    // $('#'+this.name).find('owl-prev').trigger('click');
                }).bind(this),
                error: function(){}
            });
        }
    });
});