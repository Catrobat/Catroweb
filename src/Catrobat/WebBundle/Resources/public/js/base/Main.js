var Main = function () {
  var self = this;

  $(window).ready(function() {
    self.setFooterViewListener();
    self.setHeaderMobileSearchbarListener();
  });

  self.setFooterViewListener = function() {
    $('#footer-more-less').click(function () {
      $('body').toggleClass('footer-expand');
      $(window).scrollTop($(document).height());
    });
  };

  self.setHeaderMobileSearchbarListener = function() {
    $('#menu-mobile').find('.btn-search').click(function() {
      $('nav').slide('searchbar-visible');
    });
  };
};