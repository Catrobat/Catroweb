
let termsModalAgreeButton = ''
let termsModalAgreed = false

$(document).ready(function () {
  //
  // 3 different cases to register an Pocketcode account:
  //
  // - default fos user account registration
  // - Google Sign In
  // - Facebook Sign In
  //
  $(document).on('click', '#agreeButton', function () {
    termsModalAgreed = true
    switch (termsModalAgreeButton) {
      case 'registration_form':
        $('#registration_form').submit()
        termsModalAgreeButton = ''
        break
      case 'google_login':
        triggerGoogleLogin()
        termsModalAgreeButton = ''
        break
      case 'facebook_login':
        triggerFacebookLogin()
        termsModalAgreeButton = ''
        break
    }
  })
  // Default registration
  $('#registration_form').submit(function () {
    termsModalAgreeButton = 'registration_form'
    return termsModalAgreed // only submit after agreed terms of modal
  })
  // Facebook Sign in
  $(document).on('click', '#btn-login_facebook', function () {
    termsModalAgreeButton = 'facebook_login'
  })
  // Google Sign in
  $(document).on('click', '#btn-login_google', function () {
    termsModalAgreeButton = 'google_login'
  })
})

function openDialog(defaultText = chooseUserNameDialogText, defaultInputValue = '') {
  //
  // Choose username SweetAlert2 Dialog:
  //
  // Gets called when a user sign in via OAuth, but has no account already registered.
  //
  swal({
    title: chooseUserNameDialogTitleText,
    text: defaultText,
    input: 'text',
    inputValue: defaultInputValue,
    showCancelButton: false,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: okButtonText,
    allowOutsideClick: false
  }).then(function (result) {
    if (result.length < 3 || result.length > 180) {
      // The user has to choose a valid username
      // constraints should be the same like in in: src/Resources/config/validation.xml
      openDialog(usernameInvalidSize, result)
      return
    }
    let $url = Routing.generate('catrobat_oauth_login_username_available', { flavor: flavor })
    $.post($url,
      {
        username: result
      },
      function (data) {
        if (data['username_available'] === true) {
          // The user has to choose a valid username
          return openDialog(usernameTaken, result)
        }
        // Register the user either for facebook or google
        let fbOrGoogle = $('#fb_google').val()
        if (fbOrGoogle === 'fb') {
          sendTokenToServer(
            $('#access_token_oauth').val(),
            $('#id_oauth').val(),
            result,
            $('#email_oauth').val(),
            $('#locale_oauth').val())
        } else if (fbOrGoogle === 'g+') {
          sendCodeToServer(
            $('#access_token_oauth').val(),
            $('#id_oauth').val(),
            result,
            $('#email_oauth').val(),
            $('#locale_oauth').val())
        }
      }
    )
  }).catch(swal.noop)
}
