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
      //todo: dropdown is at correct position after 3x clicking!!!
      $('#nav-dropdown').css('left', $('nav').position().left + $('nav').outerWidth() - $('#nav-dropdown').width()).toggle();
    });
  };

  self.setWindowResizeListener = function() {
    $(window).resize(function() {
      $('#nav-dropdown').hide();
    });
  };
};