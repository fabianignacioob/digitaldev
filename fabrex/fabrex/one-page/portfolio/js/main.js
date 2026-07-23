/*-----------------------------------------------------------------------------------

    Theme Name: Fabrex - One Page Portfolio HTML Template
    Description: One Page Portfolio HTML Template
    Author: Chitrakoot Web

    /* ----------------------------------

    JS Active Code Index
            
        01. Preloader
        02. scrollIt
        03. Add Class Reveal for Scroll to Top
        04. ScrollUp Active Code
        05. Sidemenu toggle
        06. Navbar scrolling background
        07. Progress bar
        08. Testimonials owlCarousel
        09. Magnific-popup
        10. Parallax
        11. Window When Loading
        12. Current Year
            
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
                navbar = $(".navbar");
                
            if(bodyScroll > 100){
                navbar.addClass("nav-scroll");
            }else{
                navbar.removeClass("nav-scroll");
            }
        });
    
         var windowsize = wind.width();
            if (windowsize <= 991) {
            $('.navbar-nav .nav-link').on("click", function(){
                $('.navbar-collapse.show').removeClass('show');
            });
          }

        /*------------------------------------
           07. Progress bar
        --------------------------------------*/

        wind.on('scroll', function () {
            $(".skills-progress span").each(function () {
                var bottom_of_object = 
                $(this).offset().top + $(this).outerHeight();
                var bottom_of_window = 
                $(window).scrollTop() + $(window).height();
                var myVal = $(this).attr('data-value');
                if(bottom_of_window > bottom_of_object) {
                    $(this).css({
                      width : myVal
                    });
                }
            });
        });

        /*------------------------------------
           08. Testimonials owlCarousel
        --------------------------------------*/

        $('.testimonials .owl-carousel').owlCarousel({
            items:1,
            loop:true,
            margin: 15,
            autoplay:true,
            smartSpeed:500
        });

        /*------------------------------------
           09. Magnific-popup
        --------------------------------------*/

        $('.gallery').magnificPopup({
            delegate: '.popimg',
            type: 'image',
            gallery: {
                enabled: true
            }
        });

        /*------------------------------------
           10. Parallax
        --------------------------------------*/

        // sections background image from data background
        var pageSection = $(".parallax,.bg-img");
        pageSection.each(function(indx) {
            if ($(this).attr("data-background")) {
                $(this).css("background-image", "url(" + $(this).data("background") + ")");
            }
        });

        /*------------------------------------
           11. Window When Loading
        --------------------------------------*/ 

        $(window).on("load",function (){

            var wind = $(window);

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
             $(window).stellar();        

        });

        $(document).ready(function() {
           
            // Default owlCarousel
            $('.owl-carousel').owlCarousel({
                items: 1,
                loop:true,
                margin: 0,
                autoplay:true,
                smartSpeed:500
            });

            /*------------------------------------
                12. Current Year
            --------------------------------------*/

            $('.current-year').text(new Date().getFullYear());
                   
        });

});
