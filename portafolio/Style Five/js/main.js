$(document) .ready(function() {

   $(".preloader-wrap").delay(1500).fadeOut('slow');

   
   var swiper = new Swiper('.swiper', {
     autoplay: {
      delay: 4000,
    },
   });

   Revealator.effects_padding = '-300';

   
   $(function() {
   $('a[href*="#"]:not([href="#"])').on('click', function() {
    if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
      var target = $(this.hash);
      target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
      if (target.length) {
        $('html, body').animate({
          scrollTop: target.offset().top
        }, 1000);
        return false;
      }
    }
   });

  $(window).scroll(function() {
    var nav = $('.navbar');
    var top = 200;
    if ($(window).scrollTop() >= top) {

        nav.addClass('inbody');

    } else {
        nav.removeClass('inbody');
    }
  }); 
 
  
  /*Magnific Popup*/
   $(function() {
    $('div.item-wrap,.gallery').magnificPopup({delegate: 'a', 
      type: 'image',
      gallery: {
        enabled: true
      },
      removalDelay: 300,
      mainClass: 'mfp-fade'
    });
   });

   $(document).ready(function() {
    $('.popup-youtube, .popup-vimeo, .popup-gmaps').magnificPopup({
        type: 'iframe',
        mainClass: 'mfp-fade',
        removalDelay: 160,
        preloader: false,
        fixedContentPos: true
    });
   });

   var filterizd = $('.filtr-container').filterizr({
      layout: 'sameSize',
      gridItemsSelector: '.filtr-item',
      gutterPixels: 20,
      selector: '.filtr-container',
      setupControls: true
   });

   $('.selector').animatedHeadline({
      animationType: 'loading-bar'
   });
   

}); 
});

