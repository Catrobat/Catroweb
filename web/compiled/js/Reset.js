/*
  Generated File by Grunt
  Sourcepath: web/js
*/
$(document).ready(function () {
    $('#reset_pw').click(function() {
      var $ajaxUrl = Routing.generate(
        'catrobat_is_oauth_user', {flavor: 'pocketcode'}
      );

      $.post($ajaxUrl,
        {
          username_email: $("#username").val(),
        },
        function (data) {
          if(data['is_oauth_user'] == true) {
            $( "#error_user_oauth" ).css("display", "block");
          } else {
            $("#_submit").click();
          }
        });

      $('#username').on('input',function(e) {
        $( "#error_user_oauth" ).css("display", "none");
      });

    });
});
