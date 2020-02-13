define(['uiComponent','jquery','ko'], function(Component,$,ko) {
    return Component.extend({
        defaults:{
        },
        initialize: function () {
            $(".open-testimonial-popup").click((function(){ this.openPopup() }).bind(this))
            this._super();
        },
        closePopup: function () {
            $('#testimonialPopup').css({'display':'none'});
        },
        validateForm: function (form) {
            return $(form).validation() && $(form).validation('isValid');
        },
        openPopup: function () {
            $('#testimonialPopup').css({'display':'flex'});
        },
        submitForm: function(){
           if (!this.validateForm('#testimonial-form')) {
               return false;
           }else{
                $('#testimonial-form').submit();
           }
        },
    });
});