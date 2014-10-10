var Main = function (search_url) {
  var self = this;
  self.search_url = search_url.replace(0, '');

  $(window).ready(function() {
    self.setClickListener();
    self.setWindowResizeListener();
  });

  self.setClickListener = function() {
    // toggle footer view
    $('#footer-more-less').click(function () {
      $('body').toggleClass('footer-expand');
      $(window).scrollTop($(document).height());
    });

    // toggle searchbar
    $('#menu-mobile').find('.btn-search').click(function() {
      $('nav').toggleClass('searchbar-visible');
      $('nav').find('input').focus();
    });

    // toggle navigation dropdown (when logged in)
    $('.show-nav-dropdown').click(function() {
      var newPosition = $('nav').position().left + $('nav').outerWidth() - $('#nav-dropdown').width();
      $('#nav-dropdown').css('left', newPosition).toggle();
    });

    self.setSearchBtnListener();
  };

  self.setWindowResizeListener = function() {
    $(window).resize(function() {
      $('#nav-dropdown').hide();
    });
  };

  self.setSearchBtnListener = function() {
    // search enter pressed
    $('.input-search').keypress(function(event) {
      if(event.which == 13) self.searchPrograms($(this).val());
    });

    // search button clicked (header)
    $('#menu').find('.img-magnifying-glass').click(function() {
      self.searchPrograms($(this).parent().parent().find('input').val());
    });

    // search button clicked (footer)
    $('#footer-menu-desktop').find('.img-magnifying-glass').click(function() {
      self.searchPrograms($(this).parent().parent().find('input').val());
    });
  };

  self.searchPrograms = function(string) {
    window.location.href = self.search_url + string;
  };
};