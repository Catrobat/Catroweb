var Help = function () {
  var self = this;

  self.setImageModal = function (path) {
    var container = $("#outer-container div");
    var overlay = $("#image-overlay");
    var popup = $("#image-popup");

    $(overlay).click(function() {
      $( overlay ).fadeToggle( 300);
      $( popup ).fadeToggle( 300);
    });

    $(popup).click(function() {
      $( overlay ).fadeToggle( 300);
      $( popup ).fadeToggle( 300);
    });

    $('.image-detail').find('img').click(function () {
      var id = $(this).data("img-id");
      var index = $(this).data("img-index");
      var type = $(this).data("img-type");

//      type: 1....hourOfCode
//            2....stepByStep
      if (id > 0) {
        if(type == 1)
          $(container).html('<img src="' + path + id + '_' + index + '.jpg" alt="" title="" />');
        else {
          if(index)
            $(container).html('<img src="' + path + id + '_' + 'right' + '_' + index + '.png" alt="" title="" />');
          else
            $(container).html('<img src="' + path + id + '_' + 'left' + '.png" alt="" title="" />');
        }


        $(container).find("img").height($(window).height() - 108);

        overlay.fadeIn(300);
        popup.fadeIn(300);
        window.scrollTo(0, 0);

        overlay.width($(document).width() - 1);
        overlay.height($(document).height());

        $(document).keyup(function (e) {
          if (e.keyCode == 27) {
            overlay.fadeOut(300, function () {
            });
            popup.fadeOut(300, function () {
            });
          }   // esc
        });
      }
      else {
        if($(this).hasClass("gif")) {
          $(this).attr("src", path + "thumbs/" + id + '_' + index + '.jpg');
          $(this).removeClass("gif")
        }
        else {
          $(this).attr("src", path + id + '_' + index + '.gif');
          $(this).addClass("gif")
        }
      }
    });
  }
};