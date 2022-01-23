import $ from 'jquery'

export function Register () {
  let termsModalAgreeButton = ''

  // -------------------------------------------------------------------------------------------------------------------
  // OAuth Sign In
  //
  $(() => {
    const $oauthRegistration = $('.js-oauth-registration')
    $(document).on('click', '#agreeButton', function () {
      if (termsModalAgreeButton === 'google_login') {
        window.location.href = $oauthRegistration.data('hwi-oauth-google')
        termsModalAgreeButton = ''
      } else if (termsModalAgreeButton === 'facebook_login') {
        window.location.href = $oauthRegistration.data('hwi-oauth-facebook')
        termsModalAgreeButton = ''
      } else if (termsModalAgreeButton === 'apple_login') {
        window.location.href = $oauthRegistration.data('hwi-oauth-apple')
        termsModalAgreeButton = ''
      }
    })
    $(document).on('click', '#btn-login-google', function () {
      termsModalAgreeButton = 'google_login'
    })
    $(document).on('click', '#btn-login-facebook', function () {
      termsModalAgreeButton = 'facebook_login'
    })
    $(document).on('click', '#btn-login-apple', function () {
      termsModalAgreeButton = 'apple_login'
    })
  })
}
