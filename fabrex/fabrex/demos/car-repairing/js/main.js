/*-----------------------------------------------------------------------------------

    Theme Name: Fabrex - Car Repair & Auto Services
    Description: Car Repair & Auto Services
    Author: Chitrakoot Web

    /* ----------------------------------

    JS Active Code Index
            
        01. Preloader
        02. Sticky Header
        03. Scroll To Top
        04. Parallax
        05. Video
        06. Date and Time Picker
        07. Sliders
        08. Countdown
        09. Current Year
        10. Gallery
        
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
                logowhite.attr('src', 'img/logos/logo-white.png');
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
            06. Date and Time Picker
        --------------------------------------*/

        $('.form_date').datetimepicker({
            language:  'en',
            weekStart: 1,
            todayBtn:  1,
            autoclose: 1,
            todayHighlight: 1,
            startView: 2,
            minView: 2,
            forceParse: 0
        });
        $('.form_time').datetimepicker({
            language:  'en',
            weekStart: 1,
            todayBtn:  1,
            autoclose: 1,
            todayHighlight: 1,
            startView: 1,
            minView: 0,
            maxView: 1,
            forceParse: 0
        });

    // === when document ready === //
    $(document).ready(function() {


        /*------------------------------------
            07. Sliders
        --------------------------------------*/

        // testmonial-carousel
        $('.testimonial-carousel').owlCarousel({
            loop: true,
            responsiveClass: true,
            nav: false,
            dots: true,
            margin: 30,
            autoplay: true,
            autoplayTimeout: 5000,
            smartSpeed:800,
            responsive: {
                0: {
                    items: 1
                },
                600: {
                    items: 1
                },
                1000: {
                    items: 2
                }

            }
        });

        // testmonial-carousel
        $('.testimonial-carousel1').owlCarousel({
            loop: true,
            responsiveClass: true,
            nav: false,
            dots: false,
            margin: 0,
            autoplay: true,
            thumbs: true,
            thumbsPrerendered: true,
            autoplayTimeout: 5000,
            smartSpeed:800,
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

        /*------------------------------------
            08. Countdown
        --------------------------------------*/

        // CountDown for coming soon page
        $(".countdown").countdown({
            date: "01 Nov 2024 00:01:00", //set your date and time. EX: 15 May 2014 12:00:00
            format: "on"
        });

        /*------------------------------------
            09. Current Year
        --------------------------------------*/

        $('.current-year').text(new Date().getFullYear());
      
    });

    // === when window loading === //
    $window.on("load", function() {

        /*------------------------------------
            10. Gallery
        --------------------------------------*/

        $('.portfolio-gallery').lightGallery();

        // stellar
        $window.stellar();

    });

})(jQuery);