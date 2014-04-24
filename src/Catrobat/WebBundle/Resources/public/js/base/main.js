var Main = function() {
    self = this;

    self.handleFooterView = function() {
        $('#footerMoreLess').click(function() {
            $('#wrapper, footer').toggleClass('expandFooter');
            $(window).scrollTop($(document).height());
        });
    };


}