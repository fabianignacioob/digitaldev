(function ($) {
    "use strict";

    var windows = $(window);
	var screenSize = windows.width();
	var sticky = $('.header-sticky');
	var $html = $('html');
	var $body = $('body');
 
    
    
    $(document).ready(function(){

        
        /*=============================================
        =            header search            =
        =============================================*/
        
        var $headerSearchToggle = $('.header-search-toggle');
        var $headerSearchForm = $('.header-search-form');
            
        $headerSearchToggle.on('click', function() {
            var $this = $(this);
            if(!$this.hasClass('open-search')) {
                $this.addClass('open-search').find('i').removeClass('fa-search').addClass('fa-times');
                $headerSearchForm.slideDown();
            } else {
                $this.removeClass('open-search').find('i').removeClass('fa-times').addClass('fa-search');
                $headerSearchForm.slideUp();
            }
        });
        
        /*=====  End of header search  ======*/
        
        

        /*=============================================
        =            menu sticky and scroll to top            =
        =============================================*/
        
        /*----------  Menu sticky ----------*/

        windows.on('scroll', function () {
            var scroll = windows.scrollTop();
            var headerHeight = 180;

            if (screenSize >= 320) {
                if (scroll < headerHeight) {
                    sticky.removeClass('is-sticky');
                } else {
                    sticky.addClass('is-sticky');
                }
            }

        });




        /*----------  Scroll to top  ----------*/

        function scrollToTop() {
            var $scrollUp = $('#scroll-top'),
                $lastScrollTop = 0,
                $window = $(window);

            $window.on('scroll', function () {
                var st = $(this).scrollTop();
                if (st > $lastScrollTop) {
                    $scrollUp.removeClass('show');
                } else {
                    if ($window.scrollTop() > 200) {
                        $scrollUp.addClass('show');
                    } else {
                        $scrollUp.removeClass('show');
                    }
                }
                $lastScrollTop = st;
            });

            $scrollUp.on('click', function (evt) {
                $('html, body').animate({scrollTop: 0}, 600);
                evt.preventDefault();
            });
        }

        scrollToTop();

        
        /*=====  End of menu sticky and scroll to top  ======*/
        
        
        /*=============================================
        =            submenu viewport position            =
        =============================================*/
    
        const menuDirChangeOnResize = () => {
            if ($(".has-sub-menu").find('.sub-menu').length) {
                var elm = $(".has-sub-menu").find('.sub-menu');
                
                elm.each(function(){
                    var off = $(this).offset();
                    var l = off.left;
                    var w = $(this).width();
                    var docH = windows.height();
                    var docW = windows.width() - 10;
                    var isEntirelyVisible = (l + w <= docW);
    
                    
                    if (!isEntirelyVisible) {
                        $(this).addClass('left');
                    }
                });
            }
        }
    
        menuDirChangeOnResize();

        /*=====  End of submenu viewport position  ======*/

        /*=============================================
        =            mobile menu active            =
        =============================================*/
        
        $("#mobile-menu-trigger").on('click', function(){
            $("#mobile-menu-overlay").addClass("active");
            $body.addClass('no-overflow');
        });
        
        $("#mobile-menu-close-trigger").on('click', function(){
            $("#mobile-menu-overlay").removeClass("active");
            $body.removeClass('no-overflow');
        });

        
        /*=====  End of mobile menu active  ======*/

        
        /*=============================================
        =            offcanvas mobile menu            =
        =============================================*/
    
        
        var $offCanvasNav = $('.offcanvas-navigation'),
        $offCanvasNavSubMenu = $offCanvasNav.find('.sub-menu-mobile');

        /*Add Toggle Button With Off Canvas Sub Menu*/
        $offCanvasNavSubMenu.parent().prepend('<span class="menu-expand"><i></i></span>');

        /*Close Off Canvas Sub Menu*/
        $offCanvasNavSubMenu.slideUp();

        /*Category Sub Menu Toggle*/
        $offCanvasNav.on('click', 'li a, li .menu-expand', function(e) {
            var $this = $(this);
            if ( ($this.parent().attr('class').match(/\b(menu-item-has-children|has-children|has-sub-menu)\b/)) && ($this.attr('href') === '#' || $this.hasClass('menu-expand')) ) {
                e.preventDefault();
                if ($this.siblings('ul:visible').length){
                    $this.parent('li').removeClass('active');
                    $this.siblings('ul').slideUp();
                } else {
                    $this.parent('li').addClass('active');
                    $this.closest('li').siblings('li').removeClass('active').find('li').removeClass('active');
                    $this.closest('li').siblings('li').find('ul:visible').slideUp();
                    $this.siblings('ul').slideDown();
                }
            }
        });


        /*=====  End of offcanvas mobile menu  ======*/

        /*=============================================
        =            background image            =
        =============================================*/
        
        var bgSelector = $(".bg-img");
        bgSelector.each(function (index, elem) {
            var element = $(elem),
                bgSource = element.data('bg');
            element.css('background-image', 'url(' + bgSource + ')');
        });
        
        /*=====  End of background image  ======*/

            
        /*=============================================
        =            counter up active            =
        =============================================*/
        
        $('.counter').counterUp({
            time: 3000
        });
        
        /*=====  End of counter up active  ======*/

        
        /*=============================================
        =            justified gallery            =
        =============================================*/
        
        $("#project-justify-wrapper").justifiedGallery({
            rowHeight : 290,
            lastRow : 'nojustify',
            margins : 3
        });
        
        /*=====  End of justified gallery  ======*/
        
        
        /*=============================================
        =            slick slider            =
        =============================================*/
        
        $('.hero-slick-slider-wrapper').slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            fade: true,
            dots: false,
            prevArrow: '<button class="slick-prev"><i class="fa fa-angle-left"></i></button>',
            nextArrow: '<button class="slick-next"><i class="fa fa-angle-right"></i></button>',
            arrows: false,
            autoplay: true,
            autoplaySpeed: 5000
        });
        
        
        $('.single-hero-slider__abs-img').slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            fade: true,
            dots: false,
            arrows: false,
            autoplay: true,
            autoplaySpeed: 3000
        });
        
        $('.service-gallery__wrapper').slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            dots: false,
            arrows: true,
            prevArrow: '<button class="slick-prev"><i class="fa fa-angle-left"></i></button>',
            nextArrow: '<button class="slick-next"><i class="fa fa-angle-right"></i></button>',
            autoplay: true,
            autoplaySpeed: 3000
        });
        
        $('.blog-gallery__wrapper').slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            dots: false,
            arrows: false,
            prevArrow: '<button class="slick-prev"><i class="fa fa-angle-left"></i></button>',
            nextArrow: '<button class="slick-next"><i class="fa fa-angle-right"></i></button>',
            autoplay: true,
            autoplaySpeed: 3000
        });
        
        $('.testimonial-slider-wrapper').slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            dots: false,
            prevArrow: '<button class="slick-prev"></button>',
            nextArrow: '<button class="slick-next"></button>',
            arrows: true,
            autoplay: true,
            autoplaySpeed: 5000
        });
        
        $('.testimonial-multi-slider-wrapper').slick({
            slidesToShow: 3,
            slidesToScroll: 1,
            dots: false,
            prevArrow: '<button class="slick-prev"></button>',
            nextArrow: '<button class="slick-next"></button>',
            arrows: true,
            autoplay: true,
            autoplaySpeed: 5000,
            centerMode: true,
            centerPadding: 0,
            responsive: [
             
                {
                    breakpoint: 1199,
                    settings: {
                        slidesToShow: 3,
                    }
                },
                {
                    breakpoint: 991,
                    settings: {
                        slidesToShow: 1
                    }
                }
            ]
        });
        
        $('.service-slider-wrapper').slick({
            slidesToShow: 3,
            slidesToScroll: 1,
            dots: false,
            arrows: false,
            autoplay: true,
            autoplaySpeed: 3000,
            responsive: [
             
			{
				breakpoint: 1199,
				settings: {
					slidesToShow: 2,
				}
			},
			{
				breakpoint: 991,
				settings: {
					slidesToShow: 2,
				}
			},
			{
				breakpoint: 767,
				settings: {
					slidesToShow: 2,
				}
			},
			{
				breakpoint: 575,
				settings: {
					slidesToShow: 2,
				}
			},
			{
				breakpoint: 479,
				settings: {
					slidesToShow: 1,
				}
			}
		]
        });
        
        $('.team-slider-wrapper').slick({
            slidesToShow: 5,
            slidesToScroll: 1,
            dots: false,
            arrows: false,
            autoplay: true,
            autoplaySpeed: 3000,
            responsive: [
             
			{
				breakpoint: 1499,
				settings: {
					slidesToShow: 4,
				}
			},
			{
				breakpoint: 1199,
				settings: {
					slidesToShow: 2,
				}
			},
			{
				breakpoint: 991,
				settings: {
					slidesToShow: 3,
				}
			},
			{
				breakpoint: 767,
				settings: {
					slidesToShow: 2,
				}
			},
			{
				breakpoint: 575,
				settings: {
					slidesToShow: 2,
				}
			},
			{
				breakpoint: 479,
				settings: {
					slidesToShow: 1,
				}
			}
		]
        });
        
        $('.brand-logo-wrapper').slick({
            slidesToShow: 5,
            slidesToScroll: 1,
            dots: false,
            arrows: false,
            autoplay: true,
            autoplaySpeed: 3000,
            responsive: [
             
			{
				breakpoint: 1199,
				settings: {
					slidesToShow: 5,
				}
			},
			{
				breakpoint: 991,
				settings: {
					slidesToShow: 4,
				}
			},
			{
				breakpoint: 767,
				settings: {
					slidesToShow: 2,
				}
			},
			{
				breakpoint: 575,
				settings: {
					slidesToShow: 2,
				}
			},
			{
				breakpoint: 479,
				settings: {
					slidesToShow: 2,
				}
			}
		]
        });
        
        /*=====  End of slick slider  ======*/
        
        
        /*=============================================
        =            ajax contact form active            =
        =============================================*/
        
        // Get the form.
        var form = $('#contact-form');

        // Get the messages div.
        var formMessages = $('.form-message');

        // Set up an event listener for the contact form.
        $(form).submit(function(e) {
            // Stop the browser from submitting the form.
            e.preventDefault();

            // Serialize the form data.
            var formData = $(form).serialize();

            // Submit the form using AJAX.
            $.ajax({
                type: 'POST',
                url: $(form).attr('action'),
                data: formData
            })
            .done(function(response) {
                // Make sure that the formMessages div has the 'success' class.
                $(formMessages).removeClass('error');
                $(formMessages).addClass('success');

                // Set the message text.
                $(formMessages).text(response);

                // Clear the form.
                $('#contact-form input,#contact-form textarea').val('');
            })
            .fail(function(data) {
                // Make sure that the formMessages div has the 'error' class.
                $(formMessages).removeClass('success');
                $(formMessages).addClass('error');

                // Set the message text.
                if (data.responseText !== '') {
                    $(formMessages).text(data.responseText);
                } else {
                    $(formMessages).text('Oops! An error occured and your message could not be sent.');
                }
            });
        });
        
        /*=====  End of ajax contact form active  ======*/

            /*=============================================
            =            mailchimp active            =
            =============================================*/
            
            $('#mc-form').ajaxChimp({
                language: 'en',
                callback: mailChimpResponse,
                // ADD YOUR MAILCHIMP URL BELOW HERE!
                url: 'https://devitems.us11.list-manage.com/subscribe/post?u=6bbb9b6f5827bd842d9640c82&amp;id=05d85f18ef'

            });

            function mailChimpResponse(resp) {

                if (resp.result === 'success') {
                    $('.mailchimp-success').html('' + resp.msg).fadeIn(900);
                    $('.mailchimp-error').fadeOut(400);

                } else if (resp.result === 'error') {
                    $('.mailchimp-error').html('' + resp.msg).fadeIn(900);
                }
            }
            
            /*=====  End of mailchimp active  ======*/

            
            /*=============================================
            =            masonry project            =
            =============================================*/
            
            var masonryProject = $('.project-wrapper--masonry');
            var grid = $('.grid-item');
            grid.hide();

            masonryProject.imagesLoaded( function() {
                grid.fadeIn();
                masonryProject.masonry({
                    itemSelector: '.grid-item',
                    columnWidth: '.gutter'
                });
            });

            
           
            /*=====  End of masonry project  ======*/

            /*=============================================
            =            light gallery active            =
            =============================================*/
            
            $('.image-popup').lightGallery({
                selector: '.single-gallery-thumb'
            }); 
            
            /*=====  End of light gallery active  ======*/
            
            

    });
    
})(jQuery);