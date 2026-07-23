/*-----------------------------------------------------------------------------------

    Theme Name: Fabrex - App Landing Template
    Description: App Landing Template
    Author: Chitrakoot Web

-----------------------------------------------------------------------------------*/

/* ----------------------------------

    JS Active Code Index
            
        01. Preloader
        02. Preloader Active code
        03. Add Class Reveal for Scroll to Top
        04. Navigation
        05. Parallax
        06. Magnific-popup Video Active Code
        07. Owl Carousel Active Code
        08. wow animation - on scroll
        09. CountUp
        10. Current Year
        11. Stellar
        
    ---------------------------------- */  


 (function ($) {

    'use strict';
    
    var $window = $(window);

        /*------------------------------------
            01. Preloader
        --------------------------------------*/

        $('#preloader').fadeOut('normall', function() {
            $(this).remove();
        });

        /*------------------------------------
            02. Preloader Active code
        --------------------------------------*/ 

        $window.on('load', function(){

        // scrollIt
        $.scrollIt({
          upKey: 38,                // key code to navigate to the next section
          downKey: 40,              // key code to navigate to the previous section
          easing: 'swing',          // the easing function for animation
          scrollTime: 600,          // how long (in ms) the animation takes
          activeClass: 'active',    // class given to the active nav element
          onPageChange: null,       // function(pageIndex) that is called when page is changed
          topOffset: -70            // offste (in px) for fixed top navigation
        });

        /*------------------------------------
            03. Add Class Reveal for Scroll to Top
        --------------------------------------*/

        $window.on('scroll', function () {
            if ($(this).scrollTop() > 500) {
                $(".scroll-to-top").fadeIn(400);
            } else {
                $(".scroll-to-top").fadeOut(400);
            }
        });

        $(".scroll-to-top").on('click', function (event) {
            event.preventDefault();
            $("html, body").animate({
                scrollTop: 0
            }, 600);
        });

        /*------------------------------------
            04. Navigation
        --------------------------------------*/

        $window.on("scroll",function () {

            var bodyScroll = $window.scrollTop(),
                navbar = $(".navbar");

            if(bodyScroll > 100){
                navbar.addClass("nav-scroll");
            }else{
                navbar.removeClass("nav-scroll");
            }
        });
    
         var windowsize = $window.width();
            if (windowsize <= 991) {
            $('.navbar-nav .nav-link').on("click", function(){
                $('.navbar-collapse.show').removeClass('show');
                $('.navbar .navbar-toggler').addClass('collapsed');
            });
          }     

        /*------------------------------------
            05. Parallax
        --------------------------------------*/

        var pageSection = $(".bg-img, section");
        pageSection.each(function(indx){
            
            if ($(this).attr("data-background")){
                $(this).css("background-image", "url(" + $(this).data("background") + ")");
            }
        });

        /*------------------------------------
            06. Magnific-popup Video Active Code
        --------------------------------------*/

        if ($.fn.magnificPopup) {
            $('.video_btn').magnificPopup({
                disableOn: 0,
                type: 'iframe',
                mainClass: 'mfp-fade',
                removalDelay: 160,
                preloader: true,
                fixedContentPos: false
            });      
        }
    
        /*------------------------------------
            07. Owl Carousel Active Code
        --------------------------------------*/

        if ($.fn.owlCarousel) {

           // App screenshot slide
            $(".app_screenshots_slides").owlCarousel({
                items: 1,
                loop: true,
                autoplay: true,
                smartSpeed: 800,
                margin: 30,
                center: true,
                dots: true,
                responsive: {
                    0: {
                        items: 1
                    },
                    576: {
                        items: 2
                    },
                    768: {
                        items: 3
                    },                
                    992: {
                        items: 4
                    },
                    1200: {
                        items: 5
                    }                
                }
            });
                           
          
            // Testmonials carousel-4
            $('.testimonial-style4').owlCarousel({
                loop: false,
                responsiveClass: true,
                items: 1,
                nav: true,
                dots: true,
                margin: 0,
                navText: ["<i class='fa fa-angle-left'></i>", "<i class='fa fa-angle-right'></i>"],
                responsive: {
                    0: {
                        items: 1,
                        nav: false,
                        dots: false
                    },              
                    768: {
                        dots: false
                    }
                }
            });
        }

        /*------------------------------------
            08. wow animation - on scroll
        --------------------------------------*/ 

        var wow = new WOW({
            boxClass: 'wow', // default
            animateClass: 'animated', // default
            offset: 0, // default
            mobile: false, // default
            live: true // default
        });
        wow.init();    


        // === when document ready === //
        $(document).ready(function() {

        /*------------------------------------
            09. CountUp
        --------------------------------------*/

        $('.countup').counterUp({
            delay: 25,
            time: 2000
        });

        /*------------------------------------
            10. Current Year
        --------------------------------------*/

        $('.current-year').text(new Date().getFullYear());
            
        });

        // === when window loading === //
        $window.on("load", function() {

        /*------------------------------------
            11. Stellar
        --------------------------------------*/
        $window.stellar();

        });

    });

})(jQuery);