var Main = function () {
  var self = this;

  self.handleFooterView = function () {
    $('#footerMoreLess').click(function () {
      $('body').toggleClass('expandFooter');
      $(window).scrollTop($(document).height());
    });
  };

  self.handleLanguage = function () {
  };
};