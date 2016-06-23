$(document).ready(function () {
    $('#btn_oauth_username').attr('disabled','disabled');
    $('#dialog_oauth_username_input').on('input',function(e) {
        $( "#error_username_taken" ).css("display", "none");
        if($('#dialog_oauth_username_input').val().trim() != '') {
            $('#btn_oauth_username').removeAttr('disabled');
            $('#btn_oauth_username').css({ opacity: 1.0});
        } else {
            $('#btn_oauth_username').attr('disabled','disabled');
            $('#btn_oauth_username').css({ opacity: 0.5});
        }
    });

    $( "#dialog-oauth-username" ).on( "dialogclose", function( event, ui ) {
        $("#bg-dark").remove();
    } );

    $('#btn_oauth_username').click(function() {

        var $ajaxUrl = Routing.generate(
            'catrobat_oauth_login_username_available', {flavor: 'pocketcode'}
        );

        $.post($ajaxUrl,
            {
                username: $("#dialog_oauth_username_input").val(),
            },
            function (data) {
                if(data['username_available'] == true) {
                    $( "#error_username_taken" ).css("display", "block");
                } else {
                    if($("#fb_google").val() == 'fb') {
                        sendTokenToServer($("#access_token_oauth").val(), $("#id_oauth").val(),
                            $("#dialog_oauth_username_input").val(), $("#email_oauth").val(), $("#locale_oauth").val());
                    } else if($("#fb_google").val() == 'g+') {
                        sendCodeToServer($("#access_token_oauth").val(), $("#id_oauth").val(),
                            $("#dialog_oauth_username_input").val(), $("#email_oauth").val(), $("#locale_oauth").val());
                    }
                }
            });


    });

    if($( "#csrf_token_oauth" ).val() == '') {
        var $ajaxUrl = Routing.generate(
            'catrobat_oauth_register_get_csrftoken', {flavor: 'pocketcode'}
        );
        $.get($ajaxUrl,
            function (data) {
                console.log(data);
                $( "#csrf_token_oauth" ).val(data['csrf_token']);
            });
    }
});

$(function() {
    var $dialogSelector = $( "#dialog-oauth-username" );
    if ($dialogSelector.length) {
        $dialogSelector.dialog({
            autoOpen: false
        });
    }
});

function openDialog() {
    $( "#dialog-oauth-username" ).dialog( "open" );

    var dark_background = $('<div id="bg-dark"></div>');
    dark_background.css({
        'position': 'fixed',
        'width': '100%',
        'height': '100%',
        'background-color': 'black',
        'left': '0',
        'top': '0',
        'opacity': '0.5'
    });

    $('body').append(dark_background);
}
