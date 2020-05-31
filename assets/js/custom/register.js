/* eslint-env jquery */

// eslint-disable-next-line no-unused-vars
const Register = function (
  flavor, chooseUserNameDialogTitleText, chooseUserNameDialogText, usernameTaken, usernameInvalidSize, okButtonText
) {
  const self = this
  self.termsModalAgreeButton = ''
  self.termsModalAgreed = false
  //
  // RegisterListener
  $(document).ready(function () {
    $(document).on('click', '#agreeButton', function () {
      self.termsModalAgreed = true
      if (self.termsModalAgreeButton === 'registration_form') {
        $('#registration_form').submit()
        self.termsModalAgreeButton = ''
      }
    })
    // Default registration
    $('#registration_form').submit(function () {
      self.termsModalAgreeButton = 'registration_form'
      return self.termsModalAgreed // only submit after agreed terms of modal
    })
  })

  // -------------------------------------------------------------------------------------------------------------------
  // Google Sign In Oauth
  //
  /* global gapi */
  /* global Swal */
  /* global Routing */
  $(document).ready(function () {
    $(document).on('click', '#agreeButton', function () {
      if (self.termsModalAgreeButton === 'google_login') {
        triggerGoogleLogin()
        self.termsModalAgreeButton = ''
      }
    })
    // Google Sign in
    $(document).on('click', '#btn-login_google', function () {
      self.termsModalAgreeButton = 'google_login'
    })
  })

  function onMySignIn (googleUser) {
    console.log('onMySignIn')
    // Useful data for your client-side scripts:
    const profile = googleUser.getBasicProfile()
    // The ID token you need to pass to your backend:
    const idToken = googleUser.getAuthResponse().idToken

    $('#email_oauth').val(profile.getEmail())
    $('#id_oauth').val(profile.getId())
    $('#access_token_oauth').val(idToken)

    checkGoogleCallbackDataWithServer()
  }

  function triggerGoogleLogin () {
    console.log('triggerGoogleLogin')
    // This option is used to allow switching between multiple google accounts!
    const options = new gapi.auth2.SigninOptionsBuilder()
    options.setPrompt('select_account')

    // Sign in
    gapi.auth2.getAuthInstance().signIn(options).then(
      function () {
        console.log('Google Login successful')
        onMySignIn(gapi.auth2.getAuthInstance().currentUser.get())
      },
      function (error) {
        console.log('Google Login failed')
        console.log(error)
      }
    )
  }

  function checkGoogleCallbackDataWithServer () {
    console.log('checkGoogleCallbackDataWithServer')

    const id = $('#id_oauth').val()
    const email = $('#email_oauth').val()
    const locale = $('#locale_oauth').val()

    const $ajaxUrlCheckServerTokenAvailable = Routing.generate(
      'catrobat_oauth_login_google_servertoken_available', { flavor: 'pocketcode' }
    )
    $.post($ajaxUrlCheckServerTokenAvailable,
      {
        id: id
      },
      function (data) {
        const serverTokenAvailable = data.token_available
        if (!serverTokenAvailable) {
          $('#id_oauth').val(id)
          $('#email_oauth').val(email)
          $('#locale_oauth').val(locale)

          const $ajaxUrlCheckEmailAvailable = Routing.generate(
            'catrobat_oauth_login_email_available', { flavor: 'pocketcode' }
          )
          $.post($ajaxUrlCheckEmailAvailable,
            {
              email: email
            },
            function (data) {
              if (data.email_available === false) {
                getDesiredUsernameGoogle()
              } else {
                sendCodeToServer($('#access_token_oauth').val(), id, data.username, email, locale)
              }
            })
        } else {
          GoogleLogin(data.email, data.username, id, locale)
        }
      })
  }

  function getDesiredUsernameGoogle () {
    console.log('getDesiredUsernameGoogle')
    $('#fb_google').val('g+')
    openDialog()
  }

  function sendCodeToServer (idToken, gplusId, username, email, locale) {
    console.log('sendCodeToServer')

    const ajaxUrl = Routing.generate(
      'catrobat_oauth_login_google_code', { flavor: 'pocketcode' }
    )

    $.post(ajaxUrl,
      {
        idToken: idToken,
        id: gplusId,
        username: username,
        email: email,
        locale: locale
      },
      function () {
        GoogleLogin(email, username, gplusId, locale)
      })
  }

  function GoogleLogin (email, username, id, locale) {
    console.log('GoogleLogin')

    const ajaxUrl = Routing.generate(
      'catrobat_oauth_login_google', { flavor: 'pocketcode' }
    )

    $.post(ajaxUrl,
      {
        username: username,
        id: id,
        email: email,
        locale: locale
      },
      function () {
        const ajaxLoginRedirectUrl = Routing.generate(
          'catrobat_oauth_login_redirect', { flavor: 'pocketcode' }
        )

        $.post(ajaxLoginRedirectUrl,
          {
            gplus_id: id
          }, function (data) {
            $(location).attr('href', data.url)
          })
      })
  }

  function openDialog (defaultText = chooseUserNameDialogText, defaultInputValue = '') {
    //
    // Choose username SweetAlert2 Dialog:
    //
    // Gets called when a user sign in via OAuth, but has no account already registered.
    //
    Swal.fire({
      title: chooseUserNameDialogTitleText,
      text: defaultText,
      input: 'text',
      inputValue: defaultInputValue,
      showCancelButton: false,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: okButtonText,
      allowOutsideClick: false,
      inputValidator: (value) => {
        if (!value || value.length < 3 || value.length > 180) {
          return usernameInvalidSize
        }
      }
    }).then((result) => {
      const username = result.value
      const $url = Routing.generate('catrobat_oauth_login_username_available', { flavor: flavor })
      $.post($url,
        {
          username: username
        },
        function (data) {
          if (data.username_available === true) {
            // The user has to choose a valid username
            return openDialog(usernameTaken, username)
          }
          // Register the user with google
          const fbOrGoogle = $('#fb_google').val()
          if (fbOrGoogle === 'g+') {
            sendCodeToServer(
              $('#access_token_oauth').val(),
              $('#id_oauth').val(),
              username,
              $('#email_oauth').val(),
              $('#locale_oauth').val())
          }
        }
      )
    })
  }
}
