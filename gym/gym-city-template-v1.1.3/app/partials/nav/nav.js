(function() {
  'use strict';

  var TOP_NAV_HEIGHT = 65;

  // to make the navbar sticky on scroll
  var navbar = $('.navbar-top');
  var windowHeight = $(window).height();

  $(window).on('scroll', function() {
    var pageScroll = $(window).scrollTop();
    if (pageScroll >= windowHeight - TOP_NAV_HEIGHT) {
      navbar.addClass('nav-scroll');
    } else {
      navbar.removeClass('nav-scroll');
    }
  });

  $(document).ready(function() {
    highlightActiveNavItem();
  });

  // This functin is for demo only!
  // Do this functionality on server side or with the help of front-end fraimwork you're using
  function highlightActiveNavItem() {
    var path = window.location.pathname.split('/').pop();
    var target = $('#gymCityNav a[href="' + path + '"]');
    target.addClass('active');

    switch (path) {
      case '':
        $('#navHome').addClass('active');
        break;

      case 'facilities.html':
      case 'team.html':
        $('#navAbout').addClass('active');
        break;

      case 'articles.html':
      case 'article.html':
        $('#navArticles').addClass('active');
        break;
    }
  }
})();
