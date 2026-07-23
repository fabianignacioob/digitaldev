/*-----------------------------------------------------------------------------------

    Theme Name: Fabrex - Wedding & Wedding Planner HTML Template
    Description: Wedding & Wedding Planner HTML Template
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
        08. Magnific-popup
        09. Countup
        10. Window When Loading
        11. Countdown
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
                navbar = $(".navbar"),
                navbloglogo = $(".blog-nav .logo> img"),
                darkbg = $(".bg-black .logo> img"),
                logo = $(".navbar .logo> img");

            if(bodyScroll > 100){
                navbar.addClass("nav-scroll");
                logo.attr('src', 'img/logo-dark.png');
                darkbg.attr('src', 'img/logo-dark.png');

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
            08. Magnific-popup
        --------------------------------------*/

        $('.appointment').magnificPopup({
            type: 'inline',
            preloader: false
        });

        /*------------------------------------
            09. Countup
        --------------------------------------*/
        
        $('.countup').counterUp({
            delay: 25,
            time: 2000
        });

        /*------------------------------------
            10. Window When Loading
        --------------------------------------*/

        $(window).on("load",function (){

            var wind = $(window);

            // stellar
            wind.stellar();

            $('.portfolio-gallery').lightGallery();

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
                        date: "01 Dec 2024 00:01:00", //set your date and time. EX: 15 May 2014 12:00:00
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