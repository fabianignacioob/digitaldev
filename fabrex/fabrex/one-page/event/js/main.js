/*-----------------------------------------------------------------------------------

    Theme Name: Fabrex - Event and Conference HTML Template
    Description: Onepage Event and Conference HTML Template
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
        08. Story Video
        09. Horizontal Tab
        10. Countup
        11. Current Year
        12. OwlCarousel Slider
        13. Window When Loading
        
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
            08. Story Video
        --------------------------------------*/

        $('.story-video').magnificPopup({
            delegate: '.video',
            type: 'iframe'
        });

        /*------------------------------------
            09. Horizontal Tab
        --------------------------------------*/

        if ($(".horizontaltab").length !== 0) {
                $('.horizontaltab').easyResponsiveTabs({
                    type: 'default', //Types: default, vertical, accordion
                    width: 'auto', //auto or any width like 600px
                    fit: true, // 100% fit in a container
                    tabidentify: 'hor_1', // The tab groups identifier
                    activate: function(event) { // Callback function if tab is switched
                        var $tab = $(this);
                        var $info = $('#nested-tabInfo');
                        var $name = $('span', $info);
                        $name.text($tab.text());
                        $info.show();
                    }
                });
            }


        /*------------------------------------
            10. Countup
        --------------------------------------*/
        
        $('.countup').counterUp({
            delay: 25,
            time: 2000
        });


        /*------------------------------------
            11. Current Year
        --------------------------------------*/

        $('.current-year').text(new Date().getFullYear());
 

        /*------------------------------------
            12. OwlCarousel Slider
        --------------------------------------*/
        
        $(document).ready(function() {

            var owl = $('.header .owl-carousel');

            // partner owlCarousel
            $('.partner .owl-carousel').owlCarousel({
                items: 1,
                loop:true,
                margin: 0,
                autoplay:false,
                dots: false,
                smartSpeed:500,
                responsive: {
                    0: {
                        items: 1
                    },
                    480: {
                        items: 2
                    },
                    767: {
                        items: 3
                    },
                    992: {
                        items: 4
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

        });

        /*------------------------------------
            13. Window When Loading
        --------------------------------------*/

        $(window).on("load",function (){

            var wind = $(window);

            // stellar
            wind.stellar();

            $('.portfolio-gallery').lightGallery();
   
    
        });

});