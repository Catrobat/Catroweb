/*
  Generated File by Grunt
  Sourcepath: web/js
*/
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

    $(document).on("click", "#btn-login_facebook", function() {
      agree_button = "facebook_login";
    });
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
    //scope: 'public_profile,email,user_about_me,manage_pages,publish_pages', manage_pages,publish_pages are necessary when requesting new token for fb post
    scope: 'public_profile,email',
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
    document.getElementById('status').innerHTML = 'Please log into Facebook';
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

    $("#id_oauth").val(response.id);
    $("#email_oauth").val(response.email);
    $("#locale_oauth").val(response.locale);

    handleFacebookUserInfoResponseTrigger();
  });
}

var handleFacebookUserInfoResponseTrigger = function handleFacebookUserInfoResponse() {

  $id = $("#id_oauth").val();
  $email = $("#email_oauth").val();
  $locale = $("#locale_oauth").val();

  var $ajaxUrlCheckServerTokenAvailable = Routing.generate(
    'catrobat_oauth_login_facebook_servertoken_available', {flavor: 'pocketcode'}
  );

  $.post($ajaxUrlCheckServerTokenAvailable,
    {
      id: $id
    },
    function (data, status) {
      console.log(data);
      console.log(status);
      var $server_token_available = data['token_available'];
      if (!$server_token_available) {

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
              getDesiredUsernameFB();
            } else {
              sendTokenToServer($("#access_token_oauth").val(), $id, data['username'], $email, $locale)
            }
          });
      } else {
        FacebookLogin(data['email'], data['username'], $id, $locale);
      }
    });
};

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
      console.log(data);
      console.log(status);
      FacebookLogin($email, $username, $facebook_id, $locale);
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
      var $ajaxLoginRedirectUrl = Routing.generate(
        'catrobat_oauth_login_redirect', {flavor: 'pocketcode'}
      );

      $.post($ajaxLoginRedirectUrl,
        {
          fb_id: $id
        }, function (data, status) {
          console.log(data);
          $url = data['url'];
          $(location).attr('href', $url);
        });

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
;$(document).ready(function () {
/*    var po = document.createElement('script');
    po.type = 'text/javascript';
    po.async = true;
    po.src = 'https://apis.google.com/js/client:plusone.js';
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(po, s);
    console.log('script added once');
 */

    $('#logout').click(function () {
        GoogleLogout();
    });

  $(document).on("click", "#btn-login_google", function() {
    if(!agree) {
      $('#btn-google-modal-trigger').click();
    }
    agree_button = "google_login";
  });
});

function triggerGoogleLogin(){
  console.log('triggerGoogleLogin');
  var $appid = '';

  var $ajaxGetGoogleAppId = Routing.generate(
    'catrobat_oauth_login_get_google_appid', {flavor: 'pocketcode'}
  );
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
  console.log('signinCallback');
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
  $('#googleLoginButton').click();
}



function getGoogleUserInfo(authResult) {
  console.log('getGoogleUserInfo');
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
  console.log('checkGoogleCallbackDataWithServer');

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
  console.log('getDesiredUsernameGoogle');
  $("#fb_google").val('g+');
  openDialog();
}

function sendCodeToServer($code, $gplus_id, $username, $email, $locale) {
  console.log('sendCodeToServer');
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
  console.log('GoogleLogin');

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
  console.log('GoogleLogout');
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

;$(document).ready(function () {
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
;function getTwitterShareUrl() {
    $twitterShareBaseUrl = 'http://twitter.com/share?url=';
    $twitterShareBaseUrl += window.location.href;
    console.log($twitterShareBaseUrl);
    return $twitterShareBaseUrl;
}

function triggerShareOnTwitter() {
    window.open(getTwitterShareUrl(), 'Twitter', 'width=490,height=530');
}

function triggerShareViaMail($programName, $programDescription, $checkoutThisProgramMessage) {
    var newLine = '%0D%0A';
    var subject = $programName;
    var body = $checkoutThisProgramMessage + ':' + newLine + window.location.href + newLine + newLine + $programDescription;

    var link = "mailto:"
            + "?subject=" + subject
            + "&body=" + body;

    window.location.href = link;
}

function appendFacebookAppIdToShareLink($facebookPlusShareBaseUrl) {
    var $ajaxGetFBAppId = Routing.generate(
        'catrobat_oauth_login_get_facebook_appid', {flavor: 'pocketcode'}
    );
    $.get($ajaxGetFBAppId,
        function (data) {
            console.log(data);

            $facebookPlusShareBaseUrl += data['fb_appid'];
            $facebookPlusShareBaseUrl += '&display=popup&href=';
            $facebookPlusShareBaseUrl += window.location.href;
            console.log($facebookPlusShareBaseUrl);

            window.open($facebookPlusShareBaseUrl, 'Facebook', 'width=490,height=530');
        });
}

function triggerShareOnFacebook() {
    $facebookPlusShareBaseUrl = 'https://www.facebook.com/dialog/share?app_id=';
    appendFacebookAppIdToShareLink($facebookPlusShareBaseUrl);
}

function getGooglePlusShareUrl() {
    $googlePlusShareBaseUrl = 'https://plus.google.com/share?url=';
    $googlePlusShareBaseUrl += window.location.href;
    console.log($googlePlusShareBaseUrl);
    return $googlePlusShareBaseUrl;
}

function triggerShareOnGooglePlus() {
    window.open(getGooglePlusShareUrl(), 'Google+', 'width=490,height=530');
}
