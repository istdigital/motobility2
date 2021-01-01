define(function () {
    'use strict';
    var mixin = {

        showError: function(message)
        {
            if (this.stripePaymentsApplePayToken() && this.config().applePayLocation == 2)
            {
                globalMessageList.addErrorMessage({ "message": message });
            }
            else
            {
                this.messageContainer.addErrorMessage({ "message": message });
            }
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});