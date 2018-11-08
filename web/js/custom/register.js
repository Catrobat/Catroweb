let agree_button = ''
let agree = false

$(document).ready(function () {
  $('#registration_form').submit(function () {
    agree_button = 'registration_form'
    return agree
  })
  
  $(document).on('click', '#agreeButton', function () {
    agree = true
    switch (agree_button)
    {
      case 'registration_form':
        console.log('agree_form_registration')
        $('#registration_form').submit()
        agree_button = ''
        break
      case 'google_login':
        console.log('agree_google_login')
        triggerGoogleLogin()
        console.log('google click')
        agree_button = ''
        break
      case 'facebook_login':
        console.log('agree_facebook_login')
        triggerFacebookLogin()
        agree_button = ''
        break
    }
  })
})
