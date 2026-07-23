/*-----------------------------------------------------------------------------------

    Theme Name: Fabrex - Fitness and GYM HTML Template
    Description: Onepage Fitness and GYM HTML Template
    Author: Chitrakoot Web

    /* ----------------------------------

    JS Active Code Index
            
        01. Preloader
        02. scrollIt
        03. Add Class Reveal for Scroll to Top
        04. ScrollUp Active Code
        05. Sidemenu toggle
        06. Navbar scrolling background
        07. Parallax
        08. Popup Video
        09. Countup
        10. Current Year
        11. OwlCarousel Slider
        
    ---------------------------------- */    

$(function() {

    "use strict";

    var wind = $(window);

        /*------------------------------------
            01. Preloader
        --------------------------------------*/

        $('#preloader').fadeOut('normall', function() {
            $(this).remove();
        });

        /*------------------------------------
            02. scrollIt
        --------------------------------------*/

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

        wind.on('scroll', function() {
            if (wind.width() > 600) {
                if (wind.scrollTop() > 600) {
                    $('#back-to-top').addClass('reveal');
                } else {
                    $('#back-to-top').removeClass('reveal');
                }
            }
        });

        /*------------------------------------
            04. ScrollUp Active Code
        --------------------------------------*/

        $('#back-to-top').on('click', function() {
            $("html, body").animate({
                scrollTop: 0
            }, 1000);
            return false;
        });

        /*------------------------------------
            05. Sidemenu toggle
        --------------------------------------*/

        if ($("#sidebar_toggle").length) {
           $("body").addClass("sidebar-menu");
           $("#sidebar_toggle").on("click", function () {
              $(".sidebar-menu").toggleClass("active");
              $(".side-menu").addClass("side-menu-active"), $("#close_sidebar").fadeIn(700)
           }), $("#close_sidebar").on("click", function () {
              $(".side-menu").removeClass("side-menu-active"), $(this).fadeOut(200), $(".sidebar-menu").removeClass("active")
           }), $("#btn_sidebar_colse").on("click", function () {
              $(".side-menu").removeClass("side-menu-active"), $("#close_sidebar").fadeOut(200), $(".sidebar-menu").removeClass("active")
           });
        }

        /*------------------------------------
            06. Navbar scrolling background
        --------------------------------------*/

        wind.on("scroll",function () {

            var bodyScroll = wind.scrollTop(),
                navbar = $(".navbar"),
                navbloglogo = $(".blog-nav .logo> img"),
                darkbg = $(".bg-black .logo> img"),
                logo = $(".navbar .logo> img");

            if(bodyScroll > 100){
                navbar.addClass("nav-scroll");
                logo.attr('src', 'img/logo-dark.png');
                darkbg.attr('src', 'img/logo-light.png');

            }else{
                navbar.removeClass("nav-scroll");
                logo.attr('src', 'img/logo-light.png');
                navbloglogo.attr('src', 'img/logo-dark.png');
            }
        });
    
         var windowsize = wind.width();
            if (windowsize <= 991) {
            $('.navbar-nav .nav-link').on("click", function(){
                $('.navbar-collapse.show').removeClass('show');
                $('.navbar .navbar-toggler').addClass('collapsed');
            });
          }

        /*------------------------------------
            07. Parallax
        --------------------------------------*/

        var pageSection = $(".bg-img, section, footer");
        pageSection.each(function(indx){
            
            if ($(this).attr("data-background")){
                $(this).css("background-image", "url(" + $(this).data("background") + ")");
            }
        });

        /*------------------------------------
            08. Popup Video
        --------------------------------------*/

        $('.story-video').magnificPopup({
            delegate: '.video',
            type: 'iframe'
        });

        /*------------------------------------
            09. Countup
        --------------------------------------*/
        
        $('.countup').counterUp({
            delay: 25,
            time: 2000
        });

        /*------------------------------------
            10. Current Year
        --------------------------------------*/

        $('.current-year').text(new Date().getFullYear());


        /*------------------------------------
            11. OwlCarousel Slider
        --------------------------------------*/
        
        $(document).ready(function() {

            var owl = $('.header .owl-carousel');

            // Slider owlCarousel
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

            // classes owlCarousel
            $('.classes-section .owl-carousel').owlCarousel({
                items: 1,
                loop:true,
                margin: 30,
                autoplay:false,
                dots: false,
                smartSpeed:500,
                responsive: {
                    0: {
                        items: 1,
                        autoplay:false,
                        margin: 10,
                    },
                    768: {
                        items: 2,
                        autoplay:false,
                        margin: 10,
                    },
                    992: {
                        items: 2,
                        margin: 10,
                    },
                    1200: {
                        items: 3,
                        margin: 20,
                    }
                }            
            });

            // Blog owlCarousel
            $('.blog-section .owl-carousel').owlCarousel({
                items: 3,
                loop:true,
                margin: 30,
                autoplay:false,
                dots: false,
                smartSpeed:500,
                responsive: {
                    0: {
                        items: 1,
                        autoplay:false,
                        margin: 10,
                    },
                    768: {
                        items: 1,
                        autoplay:false,
                        margin: 10,
                    },
                    992: {
                        items: 2,
                        margin: 10,
                    }
                }            
            });

            // Default owlCarousel
            $('.owl-carousel').owlCarousel({
                items: 1,
                loop:true,
                margin: 0,
                autoplay:true,
                smartSpeed:500
            });

            // Slider text animation
            owl.on('changed.owl.carousel', function(event) {
                var item = event.item.index - 2;     // Position of the current item
                $('span').removeClass('animated fadeInUp');
                $('h1').removeClass('animated fadeInUp');
                $('p').removeClass('animated fadeInUp');
                $('.butn').removeClass('animated fadeInUp');
                $('.owl-item').not('.cloned').eq(item).find('span').addClass('animated fadeInUp');
                $('.owl-item').not('.cloned').eq(item).find('h1').addClass('animated fadeInUp');
                $('.owl-item').not('.cloned').eq(item).find('p').addClass('animated fadeInUp');
                $('.owl-item').not('.cloned').eq(item).find('.butn').addClass('animated fadeInUp');
            });

        });

});