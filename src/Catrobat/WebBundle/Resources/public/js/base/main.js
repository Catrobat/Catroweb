var Main = function() {
    var self = this;

    self.handleFooterView = function() {
        $('#footerMoreLess').click(function() {
            $('#wrapper, footer').toggleClass('expandFooter');
            $(window).scrollTop($(document).height());
        });
    };

    self.handleLanguage = function() {

        $('#switchLanguage').on("change", function() {
            console.log("TODO: change language");
        });

    }

}