var Main = function () {
  var self = this;

  self.handleFooterView = function () {
    $('#footerMoreLess').click(function () {
      $('body').toggleClass('footerExpand');
      $(window).scrollTop($(document).height());
    });
  };

  self.handleLanguage = function () {
  };
};