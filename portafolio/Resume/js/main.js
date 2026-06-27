$(document) .ready(function() {
   /*Sidebar Menu*/
   "use strict";
   
   /*Preloader*/ 
   $(".preloader-wrap").delay(1500).fadeOut('slow');


  /*Swiper*/
   var swiper = new Swiper('.swiper', {
     spaceBetween: 30,
     autoplay: {
      delay: 4000,
     },
     breakpoints: {
       640: {
         slidesPerView: 1,
       },
       768: {
         slidesPerView: 1,
       },
       1024: {
         slidesPerView: 2,
       },
     },
   });
  
  /*Magnific Popup*/
   $(function() {
    $('div.work').magnificPopup({delegate: 'a', 
      type: 'image',
      gallery: {
        enabled: false
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
     
});

