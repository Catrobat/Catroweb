$(document).ready(function () {
    var agree_oauth = false;
    var google_triggered = false;
    var po = document.createElement('script');
    po.type = 'text/javascript';
    po.async = true;
    po.src = 'https://apis.google.com/js/client:plusone.js';
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(po, s);
    $('#logout').click(function () {
        GoogleLogout();
    });
    $("#_submit_oauth").attr("disabled", true);

    function triggerGoogleLogin(){
        var $appid = '';
        google_triggered = true;
        if(!agree_oauth)
            return false;

        var $ajaxGetGoogleAppId = Routing.generate(
          'catrobat_oauth_login_get_google_appid', {flavor: 'pocketcode'}
        );
        google_triggered = false;
        $.get($ajaxGetGoogleAppId,
          function (data) {
              console.log(data);
              var $appid = data['gplus_appid'];
              gapi.signin.render('googleLoginButton', {
                  'callback': 'signinCallback',
                  'approvalprompt': $('#gplus_approval_prompt').val(), //'force' prevents auto g+-signin
                  'clientid': $appid,
                  'cookiepolicy': 'single_host_origin',
                  'redirecturi': 'postmessage',
                  'accesstype': 'offline',
                  'scope': 'https://www.googleapis.com/auth/plus.login https://www.googleapis.com/auth/userinfo.email'
              })
          });
    }

    function signinCallback(authResult) {
        if (authResult['code']) {
            $("#access_token_oauth").val(authResult['code']);
            console.log('auth: ' + authResult['code']);
            getGoogleUserInfo(authResult);
        } else if (authResult['error']) {
            if(authResult['error'] == 'access_denied') {
                console.log('User denied access to the app');
            } else if(authResult['error'] == 'immediate_failed') {
                console.log('Automatic sign-in of user failed');
            } else {
                console.log('error:' + authResult['error']);
            }
        }
    }

    $('#agreeButton').click(function () {
        agree_oauth = true;
        if(google_triggered)
            triggerGoogleLogin();
    });

    function getGoogleUserInfo(authResult) {
        gapi.client.load('oauth2', 'v2', function () {
            var request = gapi.client.oauth2.userinfo.get();
            request.execute(getUserInfoCallback);
        });

        function getUserInfoCallback(obj) {
            if (obj['email']) {
                $("#email_oauth").val(obj['email']);
            }
            if (obj['id']) {
                $("#id_oauth").val(obj['id']);
            }
            if (obj['locale']) {
                $("#locale_oauth").val(obj['locale']);
            }
            checkGoogleCallbackDataWithServer();
        }
    }

    function checkGoogleCallbackDataWithServer() {

        $id = $("#id_oauth").val();
        $email = $("#email_oauth").val();
        $locale = $("#locale_oauth").val();

        var $ajaxUrlCheckServerTokenAvailable = Routing.generate(
          'catrobat_oauth_login_google_servertoken_available', {flavor: 'pocketcode'}
        );
        $.post($ajaxUrlCheckServerTokenAvailable,
          {
              id: $id
          },
          function (data) {
              console.log(data);
              var $server_token_available = data['token_available'];
              if (!$server_token_available) {
                  $("#id_oauth").val($id);
                  $("#email_oauth").val($email);
                  $("#locale_oauth").val($locale);

                  var $ajaxUrlCheckEmailAvailable = Routing.generate(
                    'catrobat_oauth_login_email_available', {flavor: 'pocketcode'}
                  );
                  $.post($ajaxUrlCheckEmailAvailable,
                    {
                        email: $email
                    },
                    function (data, status) {
                        console.log(data);
                        console.log(status);

                        if(data['email_available'] == false) {
                            getDesiredUsernameGoogle();
                        } else {
                            sendCodeToServer($("#access_token_oauth").val(), $id, data['username'], $email, $locale);
                        }
                    });
              } else {
                  GoogleLogin(data['email'], data['username'], $id, $locale);
              }
          });
    }

    function getDesiredUsernameGoogle() {
        $("#fb_google").val('g+');
        openDialog();
    }

    function sendCodeToServer($code, $gplus_id, $username, $email, $locale) {

        var $state = $('#csrf_token').val();
        var $ajaxUrl = Routing.generate(
          'catrobat_oauth_login_google_code', {flavor: 'pocketcode'}
        );

        $.post($ajaxUrl,
          {
              code: $code,
              id: $gplus_id,
              state: $state,
              username: $username,
              email: $email,
              locale: $locale
          },
          function (data, status) {
              console.log(status);
              console.log(data);
              GoogleLogin($email, $username, $gplus_id, $locale);
          });
    }

    function GoogleLogin($email, $username, $id, $locale) {

        var $ajaxUrl = Routing.generate(
          'catrobat_oauth_login_google', {flavor: 'pocketcode'}
        );

        $.post($ajaxUrl,
          {
              username: $username,
              id: $id,
              email: $email,
              locale: $locale
          },
          function (data, status) {
              var $ajaxLoginRedirectUrl = Routing.generate(
                'catrobat_oauth_login_redirect', {flavor: 'pocketcode'}
              );

              $.post($ajaxLoginRedirectUrl,
                {
                    gplus_id: $id
                }, function (data, status) {
                    console.log(data);
                    $url = data['url'];
                    $(location).attr('href', $url);
                });
          });
    }

    function GoogleLogout() {
        var $appid = '';
        var $ajaxGetGoogleAppId = Routing.generate(
          'catrobat_oauth_login_get_google_appid', {flavor: 'pocketcode'}
        );
        $.get($ajaxGetGoogleAppId,
          function (data) {
              var sessionParams = {
                  'client_id': data['gplus_appid'],
                  'session_state': null
              };
              gapi.auth.checkSessionState(sessionParams, function (connected) {
                  if (connected) {
                      gapi.auth.signOut()
                  }
              });
          });
    }
});

