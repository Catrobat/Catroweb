$(document).ready(function () {
    $.ajaxSetup({ cache: true });
    $.getScript('//connect.facebook.net/en_US/sdk.js', function () {
        var $appid = '';
        var $ajaxGetFBAppId = Routing.generate(
            'catrobat_oauth_login_get_facebook_appid', {flavor: 'pocketcode'}
        );
        $.get($ajaxGetFBAppId,
            function (data) {
                console.log(data);
                $appid = data['fb_appid'];
                FB.init({
                    appId: $appid,
                    xfbml: true,
                    status: true,
                    cookie: true,  //allow the server to access the session
                    version: 'v2.1'
                });
            });
    });
    $('#logout').click(function () {
        FacebookLogout();
    });
    $("#_submit_oauth").attr("disabled", true);
});

function triggerFacebookLogin() {
    FB.login(function(response) {
        if (response.authResponse) {
            console.log('Facebook Login successful');
            checkLoginState();
        } else {
            console.log('User cancelled login or did not fully authorize.');
        }
    }, {
        scope: 'public_profile,email,user_about_me',
        return_scopes: true,
        auth_type: $('#facebook_auth_type').val() //set to 'reauthenticate' to force re-authentication of the user
    });
}

function checkLoginState() {
    console.log("checkLoginState");
    FB.getLoginStatus(function (response) {
        statusChangeCallback(response);
    });
}

function statusChangeCallback(response) {
    if (response.status === 'connected') {
        $("#access_token_oauth").val(response['authResponse'].accessToken);
        getFacebookUserInfo();
    } else if (response.status === 'not_authorized') {
        // The person is logged into Facebook, but not your app.
        document.getElementById('status').innerHTML = 'Please sign in to Pocket Code';
    } else {
        // The person is not logged into Facebook, so we're not sure if
        // they are logged into this app or not.
        document.getElementById('status').innerHTML = 'Please log into Facebook.';
    }
}

function getFacebookUserInfo() {
    console.log('Welcome!  Fetching your information.... ');
    FB.api('/me', function (response) {
        console.log('Successful login for: ' + response.name);
        console.log("First name:" + response.first_name);
        console.log("Last name:" + response.last_name);
        console.log("Name:" + response.name);
        console.log("Response ID:" + response.id);
        console.log("Country:" + response.locale);

        var $ajaxUrlCheckServerTokenAvailable = Routing.generate(
            'catrobat_oauth_login_facebook_servertoken_available', {flavor: 'pocketcode'}
        );

        $.post($ajaxUrlCheckServerTokenAvailable,
            {
                id: response.id
            },
            function (data, status) {
                console.log(data);
                console.log(status);
                var $server_token_available = data['token_available'];
                if (!$server_token_available) {
                    $("#id_oauth").val(response.id);
                    $("#email_oauth").val(response.email);
                    $("#locale_oauth").val(response.locale);

                    var $ajaxUrlCheckEmailAvailable = Routing.generate(
                      'catrobat_oauth_login_email_available', {flavor: 'pocketcode'}
                    );
                    $.post($ajaxUrlCheckEmailAvailable,
                      {
                          email: response.email
                      },
                      function (data, status) {
                          console.log(data);
                          console.log(status);

                          if(data['email_available'] == false) {
                              getDesiredUsernameFB();
                          } else {
                            sendTokenToServer($("#access_token_oauth").val(), response.id, data['username'], response.email, response.locale)
                          }
                      });
                } else {
                    FacebookLogin(data['email'], data['username'], response.id, response.locale);
                }
            });
    });
}

function getDesiredUsernameFB() {
    $("#fb_google").val('fb');
    openDialog();
}

function sendTokenToServer($token, $facebook_id, $username, $email, $locale) {

    var $state = $('#csrf_token').val();

    var $ajaxUrl = Routing.generate(
        'catrobat_oauth_login_facebook_token', {flavor: 'pocketcode'}
    );

    $.post($ajaxUrl,
        {
            client_token: $token,
            id: $facebook_id,
            state: $state,
            username: $username,
            email: $email,
            locale: $locale
        },
        function (data, status) {

            $ajaxUrl = Routing.generate(
                'catrobat_oauth_login_facebook', {flavor: 'pocketcode'}
            );

            $.post($ajaxUrl,
                {
                    username: $username,
                    id: $facebook_id,
                    email: $email
                },
                function (data) {
                    submitOAuthForm(data);
                });
        });
}


function FacebookLogin($email, $username, $id, $locale) {

    var $ajaxUrl = Routing.generate(
        'catrobat_oauth_login_facebook', {flavor: 'pocketcode'}
    );

    $.post($ajaxUrl,
        {
            username: $username,
            id: $id,
            email: $email,
            locale: $locale
        },
        function (data, status) {
            submitOAuthForm(data);
        });
}

function FacebookLogout() {
    FB.getLoginStatus(function (response) {
        if (response.status === 'connected') {
            FB.logout(function (logout_response) {
                console.log('User logged out of Facebook with response:');
                console.log(logout_response);
            });
        }
    }, true);
}
