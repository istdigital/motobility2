define([
    'jquery',
    'lozad',
    'mage/smart-keyboard-handler',
    'jquery/ui',
    'mage/mage',
    'mage/ie-class-fixer',
    'rdnavbar',
    'carouselInit',
    'blockCollapse',
    'animateNumber',
    'selectize',
    'doubleTap'
], function ($,lozad) {
    'use strict';

    $.widget('TemplateMonster.theme', {

        options: {
            rdnavbar: {
                selector: ".rd-navbar",
                params: {
                    stickUpClone : false,
                    stickUpOffset: "100%",
                    responsive: {
                        0: {
                            layout: "rd-navbar-fixed",
                            navLayout: 'rd-navbar-nav-fixed',
                            stickUp: false,
                            focusOnHover: false
                        },
                        768: {
                            layout: "rd-navbar-static",
                            navLayout: 'rd-navbar-nav-fixed',
                            stickUp: false
                        },
                        1200: {
                            navLayout: 'rd-navbar-nav-fixed',
                            stickUp: false
                        }
                    }
                }
            },
            cartSummaryContainer: {
                selector: ".cart-summary",
                params: {
                    container: "#maincontent"
                }
            },
            relatedCarousel: {
                selector: ".block.related .product-items",
                params: {
                    items: 4,
                    // itemsDesktop: [1199,3],
                    // itemsDesktopSmall: [979,2],
                    // itemsTablet: [768,2],
                    // itemsMobile: [400,1],
                }
            },
            upsellCarousel: {
                selector: ".block.upsell .product-items",
                params: {
                    items: 4,
                    itemsDesktop: [1199,3],
                    itemsDesktopSmall: [979,2],
                    itemsTablet: [768,2],
                    itemsMobile: [400,1],
                }
            },
            crosssellCarousel: {
                selector: ".block.crosssell .product-items",
                params: {
                    items: 4
                }
            },
            animatedCounter: {
                selector: ".skills .number",
                speed: 2000
            },
            testimonialsCarousel: {
                selector: ".owl-testimonials",
                params: {
                    nav:true,
                    navText: false,
                    dotsSpeed: false,
                    items: 3,
                    responsive: {
                        0: {
                            items: 1
                        },
                        480: {
                            items: 1
                        },
                        768: {
                            items: 1
                        },
                        992: {
                            items: 1
                        }
                    },
                    loop : true,
                    dots : false,
                    autoPlay : true,
                    autoplay : 3000,
                    autoplayTimeout: 2000,
                    autoplayHoverPause: true
                }
            },
            messageClear: {
                selector: ".messages"
            },
            customSelect: {
                selector: "#product-options-wrapper .product-custom-option:not('.input-text'), #product-review-container select, .toolbar-posts select, select"
            }
        },

        _rdNavbar: function () {
            /* Navbar init */
            var rdNavbarData = this.options.rdnavbar;
            var o = $(rdNavbarData.selector);
            if (o.length > 0) {
                if($(".rd-navbar .layout_4").length) {
                    rdNavbarData.params.responsive[0].focusOnHover = false;
                    rdNavbarData.params.responsive[768].focusOnHover = false;
                }
                o.RDNavbar(rdNavbarData.params);
            }
        },

        _checkoutCollapsible: function() {
            if ($('body').hasClass('checkout-cart-index')) {
                if ($('#co-shipping-method-form .fieldset.rates').length > 0 && $('#co-shipping-method-form .fieldset.rates :checked').length === 0) {
                    $('#block-shipping').on('collapsiblecreate', function () {
                        $('#block-shipping').collapsible('forceActivate');
                    });
                }
            }
        },

        _cartSummaryContainer: function(){
            var cartSummaryData = this.options.cartSummaryContainer;
            $(cartSummaryData.selector).mage('sticky', cartSummaryData.params);
        },

        _sidebarCollapsible: function(){
            /* Sidebar block collapse in mobile */
            var block = $(".sidebar-additional .block");
            if(block.length > 0) {
                block.sidebarCollapse();
            }
        },

        _initProductsCarousel: function (selector) {
            var limit = $(selector).data('limit'),
                itemsCount = 1;
            if (limit != 0) {
                $('.product-item', selector).each(function(){
                    if (itemsCount > limit){
                        $(this).remove();
                    }
                    itemsCount++;
                });
            }
        },

        _productsCarousel: function () {
            /* Related init */
            var relatedCarouselData = this.options.relatedCarousel;
            this._initProductsCarousel('.block.related');
            $(relatedCarouselData.selector).carouselInit(relatedCarouselData.params);

            /* Upsell init */
            var upsellCarouselData = this.options.upsellCarousel;
            this._initProductsCarousel('.block.upsell');
            $(upsellCarouselData.selector).carouselInit(upsellCarouselData.params);

            /* Crosssell init */
            var crosssellCarouselData = this.options.crosssellCarousel;
            $(crosssellCarouselData.selector).carouselInit(crosssellCarouselData.params);
        },

        _animatedCounter: function(){
            var animatedCounterData = this.options.animatedCounter;
            var number = $(animatedCounterData.selector);
            if(number.length > 0){
                number.each(function(){
                    var finish = $(this).data('finish');
                    $(this).animateNumber({ number: finish }, animatedCounterData.speed);
                })
            }
        },

        _testimonialsCarousel: function(){
            var testimonialsData = this.options.testimonialsCarousel;
            $(testimonialsData.selector).carouselInit(testimonialsData.params);
        },

        _messageClear: function(){
            var messageClearData = this.options.messageClear;
            $(messageClearData.selector).click(function(){
                $('.message', this).hide();
            })
        },

        _toTop: function(){
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
        },

        _customSelect: function(){
            var customSelectData = this.options.customSelect;
            $(customSelectData.selector).selectize();
        },

        _faqAccordion: function () {
            $("#faq-accordion .accordion-trigger").off("click").click(function() {
                var _accTrigger = $(this);
                var _accBlock = $(_accTrigger).parent(".accordion-block");
                var _accContent = $(_accBlock).find(".accordion-content");

                if ( $(_accTrigger).hasClass( "close" ) ) {
                    $(this).removeClass("close").addClass("open");
                    $(_accBlock).removeClass("close").addClass("open");
                    $(_accContent).slideDown();
                }else{
                    $(this).removeClass("open").addClass("close");
                    $(_accBlock).removeClass("open").addClass("close");
                    $(_accContent).slideUp();
                }
            });
        },
        _doubleTapInit: function () {
            var testExp = new RegExp('Android|webOS|iPhone|iPad|' + 'BlackBerry|Windows Phone|'  + 'Opera Mini|IEMobile|Mobile' , 'i');
            if (testExp.test(navigator.userAgent)){
                setTimeout(function () {
                    $( '.rd-navbar-nav li:has(ul)' ).doubleTapToGo();
                }, 1000);
            }
        },

        _create: function() {

            const observer = lozad();
            observer.observe();

            $( document ).ajaxComplete(function() {
                $("select").selectize();
            });

            this._rdNavbar();
            this._checkoutCollapsible();
            this._cartSummaryContainer();
            this._sidebarCollapsible();
            this._productsCarousel();
            this._animatedCounter();
            //this._testimonialsCarousel();
            this._messageClear();
            //this._toTop();
            this._customSelect();
            //this._faqAccordion();
            this._doubleTapInit();

        }
    });

    return $.TemplateMonster.theme;

});