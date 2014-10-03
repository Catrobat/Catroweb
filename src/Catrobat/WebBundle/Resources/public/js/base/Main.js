var Main = function () {
  var self = this;

  $(window).ready(function() {
    self.setClickListener();
    self.setWindowResizeListener();
  });

  self.setClickListener = function() {
    $('#footer-more-less').click(function () {
      $('body').toggleClass('footer-expand');
      $(window).scrollTop($(document).height());
    });

    $('#menu-mobile').find('.btn-search').click(function() {
      $('nav').toggleClass('searchbar-visible');
      $('nav').find('input').focus();
    });

    $('.show-nav-dropdown').click(function() {
      var newPosition = $('nav').position().left + $('nav').outerWidth() - $('#nav-dropdown').width();
      $('#nav-dropdown').css('left', newPosition).toggle();
    });
  };

  self.setWindowResizeListener = function() {
    $(window).resize(function() {
      $('#nav-dropdown').hide();
    });
  };
};