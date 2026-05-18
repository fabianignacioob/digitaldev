(function() {
  'use strict';

  $(document).ready(function() {
    // init tooltips
    $('body').tooltip({ selector: '[data-toggle=tooltip]' });

    // init Animate on Scroll (https://github.com/michalsnik/aos)
    AOS.init({
      once: true,
      easing: 'ease-out-back',
      duration: '1000'
    });

    // init scrollIt (http://www.bytemuse.com/scrollIt.js)
    $.scrollIt();
  });
})();
