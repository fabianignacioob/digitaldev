/*-----------------------------------------------------------------------------------

    Theme Name: Fabrex - Health and Yoga HTML Template
    Description: Health and Yoga HTML Template
    Author: Chitrakoot Web

    /* ----------------------------------

    JS Active Code Index
            
        01. Preloader
        02. Sticky Header
        03. Scroll To Top
        04. Parallax
        05. Video
        06. Resize function
        07. FullScreenHeight function
        08. ScreenFixedHeight function
        09. FullScreenHeight and screenHeight with resize function
        10. Sliders
        11. CountUp
        12. Current year
        13. Gallery
        
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
            var logowhite = $(".navbar-brand.logowhite img");
            if (scroll <= 50) {
                $("header").removeClass("scrollHeader").addClass("fixedHeader");
                logowhite.attr('src', 'img/logos/logo.png');
            } 
            else {
                $("header").removeClass("fixedHeader").addClass("scrollHeader");
                logowhite.attr('src', 'img/logos/logo.png');
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
            05. Video
        --------------------------------------*/

        $('.story-video').magnificPopup({
            delegate: '.video',
            type: 'iframe'
        });

        /*------------------------------------
            06. Resize function
        --------------------------------------*/

        $window.resize(function(event) {
            setTimeout(function() {
                SetResizeContent();
            }, 500);
            event.preventDefault();
        });

        /*------------------------------------
            07. FullScreenHeight function
        --------------------------------------*/

        function fullScreenHeight() {
            var element = $(".full-screen");
            var $minheight = $window.height();
            element.css('min-height', $minheight);
        }

        /*------------------------------------
            08. ScreenFixedHeight function
        --------------------------------------*/

        function ScreenFixedHeight() {
            var $headerHeight = $("header").height();
            var element = $(".screen-height");
            var $screenheight = $window.height() - $headerHeight;
            element.css('height', $screenheight);
        }

        /*------------------------------------
            09. FullScreenHeight and screenHeight with resize function
        --------------------------------------*/        

        function SetResizeContent() {
            fullScreenHeight();
            ScreenFixedHeight();
        }

        SetResizeContent();

    // === when document ready === //
    $(document).ready(function() {

        /*------------------------------------
            10. Sliders
        --------------------------------------*/

        // testmonial-carousel
        $('.testimonial-carousel1').owlCarousel({
            loop: true,
            responsiveClass: true,
            nav: true,
            navText: ["<i class='fas fa-arrow-left'></i>", "<i class='fas fa-arrow-right'></i>"],
            dots: false,
            margin: 0,
            autoplay: true,
            thumbs: true,
            thumbsPrerendered: true,
            autoplayTimeout: 5000,
            smartSpeed:800,
            responsive: {
                0: {
                    items: 1,
                    nav: false
                },
                600: {
                    items: 1,
                    nav: false
                },
                1000: {
                    items: 1
                }

            }
        });

        // testmonial-carousel
        $('.testimonial-carousel').owlCarousel({
            loop: true,
            responsiveClass: true,
            autoplay: true,
            smartSpeed: 1500,
            nav: false,
            dots: false,
            center:false,
            margin: 30,
            responsive: {
                0: {
                    items: 1
                },
                768: {
                    items: 1
                },
                992: {
                    items: 2
                },

            }
        });

        // Sliderfade
        $('.slider-fade .owl-carousel').owlCarousel({
            items: 1,
            loop:true,
            dots: false,
            margin: 0,
            nav: true,
            navText: ["<i class='fas fa-chevron-left'></i>", "<i class='fas fa-chevron-right'></i>"],
            autoplay:true,
            smartSpeed:500,
            mouseDrag:false,
            animateIn: 'fadeIn',
            animateOut: 'fadeOut',
            responsive: {
                0: {
                    nav: false
                },
                768: {
                    nav: true,
                }
            }            
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
            $('span').removeClass('animated fadeInRight');
            $('h1').removeClass('animated fadeInRight');
            $('p').removeClass('animated fadeInRight');
            $('.butn').removeClass('animated fadeInRight');
            $('.owl-item').not('.cloned').eq(item).find('span').addClass('animated fadeInRight');
            $('.owl-item').not('.cloned').eq(item).find('h1').addClass('animated fadeInRight');
            $('.owl-item').not('.cloned').eq(item).find('p').addClass('animated fadeInRight');
            $('.owl-item').not('.cloned').eq(item).find('.butn').addClass('animated fadeInRight');
        });
 

        /*------------------------------------
            11. CountUp
        --------------------------------------*/

        $('.countup').counterUp({
            delay: 25,
            time: 2000
        });

        /*------------------------------------
            12. Current Year
        --------------------------------------*/

        $('.current-year').text(new Date().getFullYear());
      
    });

    // === when window loading === //
    $window.on("load", function() {

        /*------------------------------------
            13. Gallery
        --------------------------------------*/
        
        $('.portfolio-gallery').lightGallery();

        // stellar
        $window.stellar();

    });

})(jQuery);