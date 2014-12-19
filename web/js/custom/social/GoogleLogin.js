$(document).ready(function () {
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
});

function triggerGoogleLogin(){
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
                'requestvisibleactions': 'http://schemas.google.com/AddActivity',
                'redirecturi': 'postmessage',
                'accesstype': 'offline',
                'scope': 'https://www.googleapis.com/auth/plus.login https://www.googleapis.com/auth/userinfo.email'
            })
        });
}

function signinCallback(authResult) {
    if (authResult['code']) {
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

function getGoogleUserInfo(authResult) {
    gapi.client.load('oauth2', 'v2', function () {
        var request = gapi.client.oauth2.userinfo.get();
        request.execute(getUserInfoCallback);
    });

    function getUserInfoCallback(obj) {
        var $email = '';
        var $username = '';
        var $id = '';

        if (obj['email']) {
            $email = obj['email'];
        }
        if (obj['name']) {
            $username = obj['name'];
        }
        if (obj['id']) {
            $id = obj['id'];
        }
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
                    sendCodeToServer(authResult['code'], $id, $username, $email);
                } else {
                    GoogleLogin($email, $username, $id);
                }
            });
    }
}


function sendCodeToServer($code, $gplus_id, $username, $email) {

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
            mail: $email
        },
        function (data, status) {

            $ajaxUrl = Routing.generate(
                'catrobat_oauth_login_google', {flavor: 'pocketcode'}
            );

            $.post($ajaxUrl,
                {
                    username: $username,
                    id: $gplus_id,
                    mail: $email
                },
                function (data, status) {
                    submitOAuthForm(data)
                });
        });
}

function GoogleLogin($email, $username, $id) {

    var $ajaxUrl = Routing.generate(
        'catrobat_oauth_login_google', {flavor: 'pocketcode'}
    );

    $.post($ajaxUrl,
        {
            username: $username,
            id: $id,
            mail: $email
        },
        function (data, status) {
            submitOAuthForm(data)
        });
}

function submitOAuthForm(data){
    var $username = data['username'];
    var $password = data['password'];
    $("#username_oauth").val($username);
    $("#password_oauth").val($password);
    $("#_submit_oauth").attr("disabled", false);
    $("#_submit_oauth").click();
    $("#_submit_oauth").attr("disabled", true);
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