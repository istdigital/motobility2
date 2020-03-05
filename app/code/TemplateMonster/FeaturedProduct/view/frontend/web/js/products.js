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

                    const icount = $('#'+this.name).find(".owl-carousel > .item").length;
                    console.log(icount);


                    $('#'+this.name+" .owl-carousel").owlCarousel({
                        stagePadding: 5,
                        loop:false,
                        margin:40,
                        nav:true,
                        navText:["<i class='icon-arrow-prev'></i>","<i class='icon-arrow-next'></i>"],
                        responsive:{
                            0:{
                                items:1,
                                nav:false,
                                loop:true,
                            },
                            414:{
                                items:1,
                                loop:true,
                            },
                            600:{
                                items:3,
                                loop: icount > 3 ? true : false,
                            },
                            1000:{
                                items:4,
                                loop: icount > 4 ? true : false,
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