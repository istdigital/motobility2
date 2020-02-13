define(['uiComponent','jquery','ko'], function(Component,$,ko) {
    return Component.extend({
        defaults:{
        },
        initialize: function () {
            $(".open-contact-popup").click((function(){ this.openPopup() }).bind(this))
            $('#'+$('.contact-popup-recaptcha').attr("id")).appendTo('#contact-form');
            this._super();
        },
        closePopup: function () {
            $('#contactPopup').css({'display':'none'});
        },
        validateForm: function (form) {
            return $(form).validation() && $(form).validation('isValid');
        },
        openPopup: function () {
            $('#contactPopup').css({'display':'block'});
        },
        submitForm: function(){
           if (!this.validateForm('#contact-form')) {
               return false;
           }else{
                $('#contact-form').submit();
           }
        },
    });
});