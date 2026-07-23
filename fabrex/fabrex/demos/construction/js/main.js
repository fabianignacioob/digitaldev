/*-----------------------------------------------------------------------------------

    Theme Name: Fabrex - Building Construction HTML Template
    Description: Building Construction HTML Template
    Author: Chitrakoot Web

    /* ----------------------------------

    JS Active Code Index
            
        01. Preloader
        02. Sticky Header
        03. Scroll To Top
        04. Parallax
        05. Resize function
        06. FullScreenHeight function
        07. ScreenFixedHeight function
        08. FullScreenHeight and screenHeight with resize function
        09. Sliders
        10. CountUp
        11. Countdown
        12. Current year
        13. Isotop

    ---------------------------------- */    

(function($) {

    "use strict";

    var $window = $(window);

        /*------------------------------------
            01. Preloader
        --------------------------------------*/

        $('#preloader').fadeOut('normall', function() {
            $(this).remove();
        });

        /*------------------------------------
            02. Sticky Header
        --------------------------------------*/

        $window.on('scroll', function() {
            var scroll = $window.scrollTop();
            var logoinner = $(".navbar-brand img");
            if (scroll <= 50) {
                $("header").removeClass("scrollHeader").addClass("fixedHeader");
                logoinner.attr('src', 'img/logos/logo-white.png');
            } 
            else {
                $("header").removeClass("fixedHeader").addClass("scrollHeader");
                logoinner.attr('src', 'img/logos/logo.png');
            }
        });

        /*------------------------------------
            03. Scroll To Top
        --------------------------------------*/

        $window.on('scroll', function() {
            if ($(this).scrollTop() > 500) {
                $(".scroll-to-top").fadeIn(400);

            } else {
                $(".scroll-to-top").fadeOut(400);
            }
        });

        $(".scroll-to-top").on('click', function(event) {
            event.preventDefault();
            $("html, body").animate({
                scrollTop: 0
            }, 600);
        });

        /*------------------------------------
            04. Parallax
        --------------------------------------*/

        // sections background image from data background
        var pageSection = $(".parallax,.bg-img");
        pageSection.each(function(indx) {

            if ($(this).attr("data-background")) {
                $(this).css("background-image", "url(" + $(this).data("background") + ")");
            }
        });
    

        /*------------------------------------
            05. Resize function
        --------------------------------------*/

        $window.resize(function(event) {
            setTimeout(function() {
                SetResizeContent();
            }, 500);
            event.preventDefault();
        });

        /*------------------------------------
            06. FullScreenHeight function
        --------------------------------------*/

        function fullScreenHeight() {
            var element = $(".full-screen");
            var $minheight = $window.height();
            element.css('min-height', $minheight);
        }

        /*------------------------------------
            07. ScreenFixedHeight function
        --------------------------------------*/

        function ScreenFixedHeight() {
            var $headerHeight = $("header").height();
            var element = $(".screen-height");
            var $screenheight = $window.height() - $headerHeight;
            element.css('height', $screenheight);
        }

        /*------------------------------------
            08. FullScreenHeight and screenHeight with resize function
        --------------------------------------*/        

        function SetResizeContent() {
            fullScreenHeight();
            ScreenFixedHeight();
        }

        SetResizeContent();

    // === when document ready === //
    $(document).ready(function() {

        /*------------------------------------
            09. Sliders
        --------------------------------------*/

        // Testmonials carousel
        $('.testmonials-carousel').owlCarousel({
            loop: false,
            responsiveClass: true,
            nav: false,
            dots: true,
            autoplay:true,
            smartSpeed:500,            
            margin: 0,
            responsive: {
                0: {
                    items: 1
                },
                600: {
                    items: 1
                },
                1000: {
                    items: 1
                }
            }
        }) 

        // Testmonials carousel2
        $('.testimonial-style2').owlCarousel({
            loop: false,
            responsiveClass: true,
            nav: false,
            dots: true,
            autoplay:true,
            smartSpeed:500,            
            margin: 0,
            responsive: {
                0: {
                    items: 1
                },
                600: {
                    items: 1
                },
                1000: {
                    items: 2,
                    margin: 30
                }
            }
        })        

        // Carousel Style1
        $('.carousel-style1 .owl-carousel').owlCarousel({
            loop: true,
            dots: true,
            nav: false,
            navText: ["<i class='fas fa-angle-left'></i>", "<i class='fas fa-angle-right'></i>"],
            autoplay: true,
            autoplayTimeout: 5000,
            responsiveClass: true,
            autoplayHoverPause: false,
            responsive: {
                0: {
                    items: 1,
                    margin: 0,
                },
                768: {
                    items: 1,
                    margin: 0,
                },
                992: {
                    items: 1,
                    margin: 0,
                },
                1200: {
                    items: 1,
                    margin: 0,
                }
            }
        })

        // Clients carousel
        $('#clients').owlCarousel({
            loop: true,
            nav: false,
            dots: false,
            autoplay: true,
            autoplayTimeout: 3000,
            responsiveClass: true,
            autoplayHoverPause: false,
            responsive: {
                0: {
                    items: 2,
                    margin: 20
                },
                768: {
                    items: 3,
                    margin: 40,
                },
                992: {
                    items: 4,
                    margin: 60,
                    },
                    1200: {
                    items: 5,
                    margin: 80,
                }
            }
        })

        // Sliderfade
        $('.slider-fade .owl-carousel').owlCarousel({
            items: 1,
            loop:true,
            margin: 0,
            autoplay:true,
            smartSpeed:500,
            mouseDrag:false,
            animateIn: 'fadeIn',
            animateOut: 'fadeOut'
        });   

        // Default owlCarousel
        $('.owl-carousel').owlCarousel({
            items: 1,
            loop:true,
            dots: false,
            margin: 0,
            autoplay:true,
            smartSpeed:500
        });   

        // Slider text animation
        var owl = $('.slider-fade');
        owl.on('changed.owl.carousel', function(event) {
            var item = event.item.index - 2;     // Position of the current item
            $('h2').removeClass('animated fadeInUp');
            $('h1').removeClass('animated fadeInUp');
            $('p').removeClass('animated fadeInUp');
            $('.butn').removeClass('animated fadeInUp');
            $('.owl-item').not('.cloned').eq(item).find('h2').addClass('animated fadeInUp');
            $('.owl-item').not('.cloned').eq(item).find('h1').addClass('animated fadeInUp');
            $('.owl-item').not('.cloned').eq(item).find('p').addClass('animated fadeInUp');
            $('.owl-item').not('.cloned').eq(item).find('.butn').addClass('animated fadeInUp');
        });

        /*------------------------------------
            10. CountUp
        --------------------------------------*/

        $('.countup').counterUp({
            delay: 25,
            time: 2000
        });

        /*------------------------------------
            11. Countdown
        --------------------------------------*/

        if ($(".countdown").length !== 0) {
            var tpj = jQuery;
            var countdown;
            tpj(document).ready(function() {
                if (tpj(".countdown").countdown == undefined) {
                    revslider_showDoubleJqueryError(".countdown");
                } else {
                    countdown = tpj(".countdown").show().countdown({
                        date: "01 Nov 2024 00:01:00", //set your date and time. EX: 15 May 2014 12:00:00
                        format: "on"
                    });
                }
            });
        }

        /*------------------------------------
            12. Current Year
        --------------------------------------*/

        $('.current-year').text(new Date().getFullYear());
        
    });

    // === when window loading === //
    $window.on("load", function() {

        /*------------------------------------
            13. Isotop
        --------------------------------------*/

        var $PortfolioGallery = $('.portfolio-gallery-isotope').isotope({
            // options
        });

        // filter items on button click
        $('.filtering').on('click', 'span', function() {
            var filterValue = $(this).attr('data-filter');
            $PortfolioGallery.isotope({
                filter: filterValue
            });
        });

        $('.filtering').on('click', 'span', function() {
            $(this).addClass('active').siblings().removeClass('active');
        });

        $('.portfolio-gallery').lightGallery();
        $('.portfolio-gallery-isotope').lightGallery();

        // stellar
        $window.stellar();

    });

})(jQuery);
