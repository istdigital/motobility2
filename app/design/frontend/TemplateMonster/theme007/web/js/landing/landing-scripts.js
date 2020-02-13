jQuery(document).ready(function($) {
    if($("#landing-menu").length) {
        $('#landing-menu.menu li a').click(function(evt) {
            evt.preventDefault();
            $('html, body').stop().animate({
                scrollTop: $( $(this).attr('href') ).offset().top
            }, 1000);
        });
    };

    $(window).scroll(function(){
        if ($(this).scrollTop() > 400) {
            $('.scrollToTop').fadeIn();
        } else {
            $('.scrollToTop').fadeOut();
        }
    });

    $('.scrollToTop').click(function(){
        $('html, body').animate({scrollTop : 0},800);
        return false;
    });
});

jQuery(function($) {
    $(document).ready( function() {
        $('#landing-menu').stickUp();
    });

    if ($(window).width() > 1200 && $(".wow").length) {
        new WOW().init();
    }
});