$(document).ready(function () {
  
  // ToDo never called
  $('#btn-logout').click(function () {
    GoogleLogout()
  })
  
  $(document).on('click', '#btn-login_google', function () {
    if (!agree)
    {
      $('#btn-google-modal-trigger').click()
    }
    agree_button = 'google_login'
  })
})

function onMySignIn (googleUser)
{
  
  // Useful data for your client-side scripts:
  let profile = googleUser.getBasicProfile()
  // The ID token you need to pass to your backend:
  let id_token = googleUser.getAuthResponse().id_token
  
  $('#email_oauth').val(profile.getEmail())
  $('#id_oauth').val(profile.getId())
  $('#access_token_oauth').val(id_token)
  
  checkGoogleCallbackDataWithServer()
}

function triggerGoogleLogin ()
{
  
  // This option is used to allow switching between multiple google accounts!
  let options = new gapi.auth2.SigninOptionsBuilder()
  options.setPrompt('select_account')
  
  // Sign in
  gapi.auth2.getAuthInstance().signIn(options).then(
    function (success) {
      console.log('Google Login successful')
      onMySignIn(gapi.auth2.getAuthInstance().currentUser.get())
    },
    function (error) {
      console.log('Google Login failed')
      console.log(error)
    }
  )
}

function checkGoogleCallbackDataWithServer ()
{
  console.log('checkGoogleCallbackDataWithServer')
  
  $id = $('#id_oauth').val()
  $email = $('#email_oauth').val()
  $locale = $('#locale_oauth').val()
  
  let $ajaxUrlCheckServerTokenAvailable = Routing.generate(
    'catrobat_oauth_login_google_servertoken_available', {flavor: 'pocketcode'}
  )
  $.post($ajaxUrlCheckServerTokenAvailable,
    {
      id: $id
    },
    function (data) {
      let $server_token_available = data['token_available']
      if (!$server_token_available)
      {
        $('#id_oauth').val($id)
        $('#email_oauth').val($email)
        $('#locale_oauth').val($locale)
        
        let $ajaxUrlCheckEmailAvailable = Routing.generate(
          'catrobat_oauth_login_email_available', {flavor: 'pocketcode'}
        )
        $.post($ajaxUrlCheckEmailAvailable,
          {
            email: $email
          },
          function (data, status) {
            
            if (data['email_available'] === false)
            {
              getDesiredUsernameGoogle()
            }
            else
            {
              sendCodeToServer($('#access_token_oauth').val(), $id, data['username'], $email, $locale)
            }
          })
      }
      else
      {
        GoogleLogin(data['email'], data['username'], $id, $locale)
      }
    })
}

function getDesiredUsernameGoogle ()
{
  console.log('getDesiredUsernameGoogle')
  $('#fb_google').val('g+')
  openDialog()
}

function sendCodeToServer ($id_token, $gplus_id, $username, $email, $locale)
{
  console.log('sendCodeToServer')
  
  let $ajaxUrl = Routing.generate(
    'catrobat_oauth_login_google_code', {flavor: 'pocketcode'}
  )
  
  $.post($ajaxUrl,
    {
      id_token: $id_token,
      id      : $gplus_id,
      username: $username,
      email   : $email,
      locale  : $locale
    },
    function (data, status) {
      GoogleLogin($email, $username, $gplus_id, $locale)
    })
}

function GoogleLogin ($email, $username, $id, $locale)
{
  console.log('GoogleLogin')
  
  let $ajaxUrl = Routing.generate(
    'catrobat_oauth_login_google', {flavor: 'pocketcode'}
  )
  
  $.post($ajaxUrl,
    {
      username: $username,
      id      : $id,
      email   : $email,
      locale  : $locale
    },
    function (data, status) {
      let $ajaxLoginRedirectUrl = Routing.generate(
        'catrobat_oauth_login_redirect', {flavor: 'pocketcode'}
      )
      
      $.post($ajaxLoginRedirectUrl,
        {
          gplus_id: $id
        }, function (data, status) {
          $url = data['url']
          $(location).attr('href', $url)
        })
    })
}

// ToDo never called
function GoogleLogout ()
{
  console.log('GoogleLogout')
  let $appid = ''
  let $ajaxGetGoogleAppId = Routing.generate(
    'catrobat_oauth_login_get_google_appid', {flavor: 'pocketcode'}
  )
  $.get($ajaxGetGoogleAppId,
    function (data) {
      let sessionParams = {
        'client_id'    : data['gplus_appid'],
        'session_state': null
      }
      gapi.auth.checkSessionState(sessionParams, function (connected) {
        if (connected)
        {
          gapi.auth.signOut()
        }
      })
    })
}

