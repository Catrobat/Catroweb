/*
  Generated File by Grunt
  Sourcepath: web/js
*/
var Help = function () {
  var self = this;

  self.setImageModal = function (path) {
    var container = $("#outer-container div");
    var overlay = $("#image-overlay");
    var popup = $("#image-popup");

    var large_popups = [21];

    overlay.click(function() {
      overlay.fadeToggle( 300);
      popup.fadeToggle( 300);
    });

    popup.click(function() {
      overlay.fadeToggle( 300);
      popup.fadeToggle( 300);
    });

    $('.image-detail').find('img').click(function () {
      var id = $(this).data("img-id");
      var index = $(this).data("img-index");
      var type = $(this).data("img-type");

//      type: 1....hourOfCode
//            2....stepByStep
//            3....game jam
      if (id > 0) {
        if(type == 1)
          $(container).html('<img src="' + path + id + '_' + index + '.jpg" alt="" title="" />');
        else if(type == 3)
          $(container).html('<img src="' + path + index + '.png" alt="" title="" />');
        else {
          if(index)
            $(container).html('<img src="' + path + id + '_' + 'right' + '_' + index + '.png" alt="" title="" />');
          else
            $(container).html('<img src="' + path + id + '_' + 'left' + '.png" alt="" title="" />');
        }

        if(large_popups.indexOf(id) != -1) {
          popup.addClass('large');
        }
        else {
          $(container).find("img").height($(window).height() - 108);
        }

        overlay.fadeIn(300);
        popup.fadeIn(300);
        if(type != 3)
          window.scrollTo(0, 0); // why scroll to top?

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