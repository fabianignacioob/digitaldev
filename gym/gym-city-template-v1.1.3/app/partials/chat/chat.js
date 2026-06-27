(function() {
  'use strict';

  $(window).on('load', function() {
    setTimeout(showLiveChat, 1000);
  });

  function showLiveChat() {
    $('.chat').addClass('visible');
  }
})();
