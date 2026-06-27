(function() {
  'use strict';

  // show loading screen until the page is loaded
  $(window).on("load", function() {
    $("#loadingScreen").fadeOut(300);
  });
})();
