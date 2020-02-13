define(['uiComponent','jquery','ko','jquery/ui'], function(Component,$,ko,select2) {
    return Component.extend({
        defaults:{
            term: 0,
            _term: ko.observable(0),

            _productID: ko.observable(null),
            _productName: ko.observable(null),

            fee: 75,
            payment_charge: 2.50,
            _price: ko.observable(null),
            _deposit: ko.observable(null),

            price: null,
            deposit: null,

            total: ko.observable(0.00),
            subtotal: ko.observable(0.00),
            error: ko.observable(false),

            isDepositDisable: ko.observable(true),
            isTermDisable: ko.observable(true),
            isContactDisable: ko.observable(true)
        },
        clearPrice: function(data, event){
            this._price(null);
            this._deposit(null);
            this.error(false)
            this.isDepositDisable(true);
            this.isTermDisable(true);
            this.isContactDisable(true);

        },
        initialize: function () {
            
            

            //$("a.open-repayment-popup").click((function(){this.openPopup();}).bind(this));
            $("a.open-repayment-popup").click(function(){
                $('#rCalculator, .rcalculator-form-wrap').css({'display':'block'});
            });

            this.postUrl = BASE_URL + 'repayment/index/post';
            this._super();


            this._price.subscribe((function(v) { this.price = parseFloat(v); }).bind(this));
            this._deposit.subscribe((function(v) { this.deposit = parseFloat(v) }).bind(this));
            this._term.subscribe((function(v) { this.term = v }).bind(this));

            var i = parseInt($("meta[itemprop='mpn']").attr('content'));
            if(!isNaN(i)){
                this._productID(i);
            }

            if($("span[itemprop='name']").length)
                this._productName($("span[itemprop='name']").text());

            

            var p = parseFloat($("meta[itemprop='price']").attr('content'));
            if(!isNaN(p)){
                this._price(p);
                this._deposit((p * 0.10).toFixed(2));
                this.isDepositDisable(false);
                this.isTermDisable(false);
            }

            

        },
        validateForm: function (form) {
            return $(form).validation() && $(form).validation('isValid');
        },
        submitForm: function(){
           if (!this.validateForm('#contact-form')) {
               return false;
           }else{
                $('#contact-form').submit();
           }
        },
        openContactPopup: function () {

            this.closePopup();
            $('#rCalculator, .contact-form-wrap').css({'display':'block'});
        },
        closeContactPopup: function () {
            $('#rCalculator, .contact-form-wrap').css({'display':'none'});
        },
        openPopup: function () {
            $('#rCalculator, .rcalculator-form-wrap').css({'display':'block'});
        },
        closePopup: function () {
            $('#rCalculator, .rcalculator-form-wrap').css({'display':'none'});
        },

        
        Calculate: function (data, event) {

            var price = parseFloat(this.price);
            var min_deposit = parseFloat((price * 0.10).toFixed(2));
            var deposit = isNaN(parseFloat(this.deposit)) ? 0 : parseFloat(this.deposit);
            //var deposit = parseFloat(this.deposit);

            
            if(event.target.id == 'price'){ 
                this._deposit(min_deposit);
                deposit = min_deposit;
            }

            if(deposit < min_deposit){
                this.error(true);
            }else{
                this.error(false);
            }

            if(price > 0){
                this.isDepositDisable(false);    
            }else{
                this.isDepositDisable(true);    
            }


            if(price > 0 && deposit >= min_deposit){
                this.isTermDisable(false);    
            }else{
                this.isTermDisable(true);    
            }

            

            if(deposit >= min_deposit && this.term != 0 && price > 0){
                var Mtotal1 = price + parseFloat(this.fee); 
                var MTotal2 = Mtotal1 - deposit;
                var MTotal3 = MTotal2 / parseInt(this.term);
                this.subtotal((MTotal3).toFixed(2));
                var Finaltotal = MTotal3 + parseFloat(this.payment_charge);
                this.total((Finaltotal).toFixed(2));

                this.isContactDisable(false);
            }else{
                this.subtotal((0).toFixed(2));
                this.total((0).toFixed(2));
                this.isContactDisable(true);
            }
            
        }
    });
});