var Main = function () {
  var self = this;

  self.handleFooterView = function() {
    $('#footer-more-less').click(function () {
      $('body').toggleClass('footer-expand');
      $(window).scrollTop($(document).height());
    });
  };

  self.initialize = function() {
    self.handleFooterView();
  };
  self.initialize();
};