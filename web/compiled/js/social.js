/*
  Generated File by Grunt
  Sourcepath: web/js
*/
let agree_button = ''
let agree = false

$(document).ready(function () {
  $(document).on('click', '#agreeButton', function () {
    agree = true
    switch (agree_button)
    {
      case 'google_login':
        console.log('agree_google_login')
        triggerGoogleLogin()
        break
      case 'facebook_login':
        console.log('agree_facebook_login')
        triggerFacebookLogin()
        break
    }
  })
})
