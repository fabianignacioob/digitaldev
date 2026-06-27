(function($) {
  'use strict';

  $(window).on('load', function () {
    $('#preloader').fadeOut(300);
  });

  //==============================1. NAVBAR =========================

  $(window).on('load', function(){

    var header_area = $('.header');
    var main_area = header_area.find('.navbar');

    $(window).scroll(function(){
      if (($(this).scrollTop() <= 400 || $(this).width() <= 600)) {
        main_area.removeClass('navbar-sticky');
      } else {
        main_area.addClass('navbar-sticky');
      }

    });

    $(window).trigger('resize');
    $(window).trigger('scroll');


  });

  //==============================3. ALL DROPDOWN ON HOVER =========================
    if ($('.navbar').width() > 768) {
      $('.navbar-nav .dropdown').hover(function () {
        $(this).add($(this).children('.dropdown-menu')).addClass('show');
      },
      function () {
        $(this).add($(this).children('.dropdown-menu')).removeClass('show');
      });
    }

    $('.nav-search .search-trigger').click(function () {
      $('.nav-search').toggleClass('show');
    });


  //==============================5. MAIN SLIDER =========================
  $('#homeCarousel').owlCarousel({
    loop: true,
    dots: true,
    animateOut: 'fadeOut',
    animateIn: 'fadeIn',
    responsive: {
      0: {
        items: 1,
        dots: false
      },
      600: {
        items: 1,
        dots: false
      },
      1000: {
        items: 1
      }
    }
  });
  $('.carousel-style-one').owlCarousel({
    loop: true,
    dots: true,
    animateOut: 'fadeOut',
    animateIn: 'fadeIn',
    responsive: {
      0: {
        items: 1,
        dots: false
      },
      600: {
        items: 1,
        dots: false
      },
      1000: {
        items: 1
      }
    }
  });
$('#homeCarousel').on('translate.owl.carousel', function(){
  $('.carousel-description').removeClass('animated').hide();
});

$('#homeCarousel').on('translated.owl.carousel', function(){
  $('.owl-item.active .carousel-description').addClass('animated').show();
});
  //==============================6. Service Carousel =========================
  $('#service-carousel').owlCarousel({
    loop: true,
    dots: true,
    items: 3,
    margin: 30,
    responsive: {
      0: {
        items: 1,
        margin: 0
      },
      540: {
        items: 1,
        margin: 20
      },
      720: {
        items: 2
      },
      960: {
        items: 3
      },
      1140: {
        items: 3
      }
    }
  });

  //==============================7. Testimonial Carousel =========================
  $('.owl-carousel.testimonial-carousel').owlCarousel({
    loop: true,
    dots: true,
    dotsData: true,
    startPosition:1,
    center: true,
    nav: true,
    navText: ['&#8592;', '&#8594;'],
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
//==============================8. Brand Carousel =========================
  $('#brands').owlCarousel({
    loop: true,
    dots: false,
    items: 4,
    margin: 10,
    autoplay: true,
    responsive: {
      0: {
        items: 1,
        margin: 0
      },
      540: {
        items: 2,
        margin: 20
      },
      720: {
        items: 3
      },
      960: {
        items: 3
      },
      1140: {
        items: 4,
        margin: 10
      }
    }
  });
//==============================9. Element Carousel =========================
  $('#activeCarousel').owlCarousel({
    loop: true,
    dots: true,
    startPosition:1,
    responsive: {
      0: {
        items: 1,
        dots: false
      },
      600: {
        items: 1,
        dots: false
      },
      1000: {
        items: 1
      }
    }
  });


  //============================10. LAZY LOAD =========================

	$('.scroll-top').click(function(){
      $('html, body').animate({ scrollTop: 0 }, 1500);
      return false;
   });

  //==============================11. MAP =========================

  function initialize() {
    var styleArray = [
      {
        'featureType': 'administrative',
        'elementType': 'labels.text.fill',
        'stylers': [
          {
            'color': '#444444'
          }
        ]
      },
      {
        'featureType': 'landscape',
        'elementType': 'all',
        'stylers': [
          {
            'color': '#f2f2f2'
          }
        ]
      },
      {
        'featureType': 'poi',
        'elementType': 'all',
        'stylers': [
          {
            'visibility': 'off'
          }
        ]
      },
      {
        'featureType': 'road',
        'elementType': 'all',
        'stylers': [
          {
            'saturation': -100
          },
          {
            'lightness': 45
          }
        ]
      },
      {
        'featureType': 'road.highway',
        'elementType': 'all',
        'stylers': [
          {
            'visibility': 'simplified'
          }
        ]
      },
      {
        'featureType': 'road.arterial',
        'elementType': 'labels.icon',
        'stylers': [
          {
            'visibility': 'off'
          }
        ]
      },
      {
        'featureType': 'transit',
        'elementType': 'all',
        'stylers': [
          {
            'visibility': 'off'
          }
        ]
      },
      {
        'featureType': 'water',
        'elementType': 'all',
        'stylers': [
          {
            'color': '#46bcec'
          },
          {
            'visibility': 'on'
          }
        ]
      },
      {
        'featureType': 'water',
        'elementType': 'geometry.fill',
        'stylers': [
          {
            'color': '#bfd3e4'
          }
        ]
      }
    ];

    var myLatLng = { lat: 40.6094957, lng: -73.7300059 };

    var mapOptions = {
      zoom: 14,
      scrollwheel: false,
      center: new google.maps.LatLng(40.6094957, -73.7300059),
      styles: styleArray
    };

    var map = new google.maps.Map(document.getElementById('googleMap'), mapOptions);
    var image = 'img/small-img/logo-icons/marker.png';
    var marker = new google.maps.Marker({
      position: myLatLng,
      map: map,
      icon: image
    });
  }
  var mapId = $('#googleMap');
  if (mapId.length) {
    google.maps.event.addDomListener(window, 'load', initialize);
  }

  //==============================12. ANIMATION =========================
  var $animation_elements = $('.animated');
  var $window = $(window);

  function check_if_in_view() {
    var window_height = $window.height();
    var window_top_position = $window.scrollTop();
    var window_bottom_position = (window_top_position + window_height);

    $.each($animation_elements, function () {
      var $element = $(this);
      var element_height = $element.outerHeight();
      var element_top_position = $element.offset().top;
      var element_bottom_position = (element_top_position + element_height);
      var animationType = $(this).attr('data-animation');

      //check to see if this current container is within viewport
      if ((element_bottom_position >= window_top_position) && (element_top_position <= window_bottom_position)) {
        $element.addClass(animationType);
      } else {
        $element.removeClass(animationType);
      }
    });
  }

  $window.on('scroll resize', check_if_in_view);
  $window.trigger('scroll');


  //===========================14. SYOTIMER ==========================
  var simple_timer = $('.simple_timer');
  if (simple_timer){
      simple_timer.syotimer({
      year: 2020,
      month: 9,
      day: 9,
      hour: 20,
      minute: 30
    });
  }

})(jQuery);
